<?php
// TextToSqlService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class TextToSqlService
{
    protected $apiKey;
    protected $apiUrl;
    protected $dbSchema;

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_API_KEY'); // Sử dụng Google API key
        $this->apiUrl = env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');
        $this->dbSchema = $this->getDbSchema();
    }

    /**
     * Tìm kiếm sản phẩm bằng natural language
     */
    public function searchProducts($naturalLanguageQuery)
    {
        try {
            // Tạo SQL từ natural language
            $sql = $this->generateSql($naturalLanguageQuery);

            if (!$sql) {
                // Fallback: tìm kiếm đơn giản
                return $this->fallbackSearch($naturalLanguageQuery);
            }

            // Thực thi SQL và lấy kết quả
            $results = DB::select($sql);

            // Chuyển đổi kết quả thành model instances
            $productIds = collect($results)->pluck('id')->unique();

            return Product::with(['category', 'productVariants.size', 'productVariants.crust'])
                ->whereIn('id', $productIds)
                ->get();

        } catch (\Exception $e) {
            Log::error('Text-to-SQL error: ' . $e->getMessage());
            return $this->fallbackSearch($naturalLanguageQuery);
        }
    }

    /**
     * Tạo SQL từ natural language
     */
    private function generateSql($query)
    {
        try {
            $prompt = $this->buildPrompt($query);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $this->getSystemPrompt()
                                    ],
                                    [
                                        'text' => $prompt
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'maxOutputTokens' => 500,
                            'temperature' => 0.1
                        ]
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                $sqlQuery = trim($data['candidates'][0]['content']['parts'][0]['text']);

                // Làm sạch SQL query
                $sqlQuery = $this->cleanSqlQuery($sqlQuery);

                // Validate SQL
                if ($this->validateSql($sqlQuery)) {
                    return $sqlQuery;
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Generate SQL error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * System prompt cho AI
     */
    private function getSystemPrompt()
    {
        return "You are a SQL expert. Convert natural language queries about pizza products to SQL queries.
        Rules:
        1. Only return the SQL query, no explanations
        2. Always SELECT DISTINCT p.id, p.name, p.description, p.image_url 
        3. Always include proper JOINs with categories, sizes, crusts as needed
        4. Use Vietnamese keywords mapping: 
           - 'pizza' -> categories.name LIKE '%pizza%'
           - 'hải sản' -> p.name LIKE '%hải sản%' OR p.description LIKE '%hải sản%'
           - 'chay' -> p.name LIKE '%chay%' OR p.description LIKE '%chay%'
           - 'thịt' -> p.name LIKE '%thịt%' OR p.description LIKE '%thịt%'
           - 'phô mai' -> p.name LIKE '%phô mai%' OR p.description LIKE '%phô mai%'
           - 'lớn' -> s.name IN ('Lớn', 'Siêu Lớn', 'Gia Đình')
           - 'nhỏ' -> s.name IN ('Nhỏ', 'Mini')
           - 'mỏng' -> c.name LIKE '%mỏng%'
           - 'dày' -> c.name LIKE '%dày%'
        5. Always add WHERE conditions to filter active products
        6. Limit results to 20";
    }

    /**
     * Xây dựng prompt
     */
    private function buildPrompt($query)
    {
        return "Database schema:\n" . $this->dbSchema . "\n\n" .
            "Convert this Vietnamese query to SQL: \"$query\"\n\n" .
            "Requirements:\n" .
            "- Search in products table and related tables\n" .
            "- Use LIKE for text searches with Vietnamese terms\n" .
            "- Include proper JOINs\n" .
            "- Return product information that can be added to cart";
    }

    /**
     * Lấy database schema
     */
    private function getDbSchema()
    {
        return "
        CREATE TABLE products (
            id INT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            image_url VARCHAR(255),
            category_id INT,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        );

        CREATE TABLE categories (
            id INT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT
        );

        CREATE TABLE product_variants (
            id INT PRIMARY KEY,
            product_id INT NOT NULL,
            size_id INT,
            crust_id INT,
            price DECIMAL(10,2) NOT NULL,
            stock INT DEFAULT 100,
            FOREIGN KEY (product_id) REFERENCES products(id),
            FOREIGN KEY (size_id) REFERENCES sizes(id),
            FOREIGN KEY (crust_id) REFERENCES crusts(id)
        );

        CREATE TABLE sizes (
            id INT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            diameter DECIMAL(5,2)
        );

        CREATE TABLE crusts (
            id INT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            description TEXT
        );";
    }

    /**
     * Làm sạch SQL query
     */
    private function cleanSqlQuery($sql)
    {
        // Loại bỏ markdown formatting
        $sql = preg_replace('/```sql\n?/', '', $sql);
        $sql = preg_replace('/```\n?/', '', $sql);

        // Loại bỏ comments
        $sql = preg_replace('/--.*$/m', '', $sql);

        // Trim whitespace
        $sql = trim($sql);

        // Đảm bảo kết thúc bằng semicolon
        if (!str_ends_with($sql, ';')) {
            $sql .= ';';
        }

        return $sql;
    }

    /**
     * Validate SQL query
     */
    private function validateSql($sql)
    {
        // Basic validation
        if (empty($sql)) {
            return false;
        }

        // Chỉ cho phép SELECT queries
        if (!preg_match('/^\s*SELECT\s+/i', $sql)) {
            return false;
        }

        // Không cho phép các từ khóa nguy hiểm
        $dangerousKeywords = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE', 'TRUNCATE'];
        foreach ($dangerousKeywords as $keyword) {
            if (stripos($sql, $keyword) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fallback search khi text-to-SQL thất bại
     */
    private function fallbackSearch($query)
    {
        $searchTerms = explode(' ', strtolower($query));

        return Product::with(['category', 'productVariants.size', 'productVariants.crust'])
            ->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('name', 'LIKE', "%$term%")
                        ->orWhere('description', 'LIKE', "%$term%");
                }
            })
            ->orWhereHas('category', function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('name', 'LIKE', "%$term%");
                }
            })
            ->limit(20)
            ->get();
    }
}