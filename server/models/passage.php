<?php

class PassageModel {
    private $db;

    /**
     * Khởi tạo model với kết nối PDO
     */
    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Tạo passage (đoạn văn/audio dùng chung cho nhóm câu hỏi)
     * 
     * @param array $data - Chứa: test_id (bắt buộc), content, audio_url, image_url
     * @return int - ID của passage vừa tạo
     * @throws Exception
     */
    public function create($data) {
        try {
            // Validate required fields
            if (empty($data['test_id'])) {
                throw new Exception("test_id là bắt buộc");
            }

            // Chuẩn bị câu lệnh SQL
            $sql = "INSERT INTO passages 
                    (test_id, content, audio_url, image_url) 
                    VALUES 
                    (:test_id, :content, :audio_url, :image_url)";

            $stmt = $this->db->prepare($sql);

            // Bind dữ liệu
            $result = $stmt->execute([
                ':test_id' => $data['test_id'] ?? null,
                ':content' => $data['content'] ?? null,
                ':audio_url' => $data['audio_url'] ?? null,
                ':image_url' => $data['image_url'] ?? null,
            ]);

            if (!$result) {
                throw new Exception("Lỗi khi lưu passage");
            }

            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("Lỗi tạo passage: " . $e->getMessage());
        }
    }

    /**
     * Lấy passage theo ID
     * 
     * @param int $passageId
     * @return array|null - Thông tin passage hoặc null nếu không tìm thấy
     */
    public function getById($passageId) {
        try {
            $sql = "SELECT id, test_id, content, audio_url, image_url 
                    FROM passages 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $passageId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : null;
        } catch (Exception $e) {
            throw new Exception("Lỗi khi lấy passage: " . $e->getMessage());
        }
    }

    /**
     * Lấy tất cả passages của một test
     * 
     * @param int $testId
     * @return array - Danh sách passages
     */
    public function getByTestId($testId) {
        try {
            $sql = "SELECT id, test_id, content, audio_url, image_url 
                    FROM passages 
                    WHERE test_id = :test_id 
                    ORDER BY id ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':test_id' => $testId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Lỗi khi lấy passages: " . $e->getMessage());
        }
    }

    /**
     * Cập nhật passage
     * 
     * @param int $passageId
     * @param array $data - Chứa các field cần cập nhật
     * @return bool
     */
    public function update($passageId, $data) {
        try {
            $updates = [];
            $params = [':id' => $passageId];

            if (isset($data['content'])) {
                $updates[] = "content = :content";
                $params[':content'] = $data['content'];
            }

            if (isset($data['audio_url'])) {
                $updates[] = "audio_url = :audio_url";
                $params[':audio_url'] = $data['audio_url'];
            }

            if (isset($data['image_url'])) {
                $updates[] = "image_url = :image_url";
                $params[':image_url'] = $data['image_url'];
            }

            if (empty($updates)) {
                return true;
            }

            $sql = "UPDATE passages SET " . implode(", ", $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
        } catch (Exception $e) {
            throw new Exception("Lỗi khi cập nhật passage: " . $e->getMessage());
        }
    }

    /**
     * Xóa passage
     * 
     * @param int $passageId
     * @return bool
     */
    public function delete($passageId) {
        try {
            $sql = "DELETE FROM passages WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([':id' => $passageId]);
        } catch (Exception $e) {
            throw new Exception("Lỗi khi xóa passage: " . $e->getMessage());
        }
    }

    /**
     * Kiểm tra passage có tồn tại không
     * 
     * @param int $passageId
     * @param int $testId (optional) - Kiểm tra passage có thuộc test này không
     * @return bool
     */
    public function exists($passageId, $testId = null) {
        try {
            if ($testId !== null) {
                $sql = "SELECT 1 FROM passages WHERE id = :id AND test_id = :test_id LIMIT 1";
                $params = [':id' => $passageId, ':test_id' => $testId];
            } else {
                $sql = "SELECT 1 FROM passages WHERE id = :id LIMIT 1";
                $params = [':id' => $passageId];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception("Lỗi khi kiểm tra passage: " . $e->getMessage());
        }
    }

    /**
     * Lấy số lượng passages của một test
     * 
     * @param int $testId
     * @return int
     */
    public function countByTestId($testId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM passages WHERE test_id = :test_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':test_id' => $testId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (Exception $e) {
            throw new Exception("Lỗi khi đếm passages: " . $e->getMessage());
        }
    }
}
