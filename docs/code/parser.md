# Pipeline đưa đề thi vào hệ thống

## Cần chuẩn bị

1. Lên trang web đề thi -> lưu trang HTML (Save as "Web Page, Complete")
2. Bỏ file HTML đề vào thư mục `tests/`
3. Bỏ file HTML đáp án vào thư mục `answer/` **(cùng tên file với đề)**
4. Không cần đổi tên file

---

## Bước 1 - Parse đề thi

```bash
php scripts/parser.php \
  --input "tests/[tên file đề].html" \
  --out "tests/[slug].json"
```

Output JSON gồm: title, duration, audio_url, parts 1-7, passages, questions, options

---

## Bước 2 - Import đề vào database

```bash
php scripts/import_exam.php --json "tests/[slug].json"
```

Script sẽ:
- Tạo bản ghi `tests` (is_active = 0, draft mode)
- Copy ảnh từ `tests/[tên]_files/` -> `server/uploads/image/[slug]/question_X.ext`
- Insert `passages`, `questions`, `options`
- In ra **test_id** cần dùng ở bước 4

---

## Bước 3 - Parse đáp án

```bash
php scripts/parser.php \
  --mode answer \
  --input "answer/[tên file đáp án].html" \
  --out "tests/[slug]_answers.json"
```

Output JSON: `{ exam_title, answers: [{ question_number, correct_answer, passage_translation, options }] }`

---

## Bước 4 - Import đáp án + kích hoạt đề

```bash
php scripts/import_answers.php \
  --json "tests/[slug]_answers.json" \
  --test-id <id từ bước 2> \
  --activate
```

`--activate`: tự đặt `is_active = 1` sau khi import xong

---

## Verify

```bash
# kiểm tra số câu đã có đáp án hợp lệ
php -r '
$pdo = new PDO("mysql:host=127.0.0.1;dbname=prephub;charset=utf8mb4", "prephub", "123");
$s = $pdo->prepare("SELECT part, COUNT(*) as total, SUM(correct_answer IN (\"A\",\"B\",\"C\",\"D\")) as valid FROM questions WHERE test_id = ? GROUP BY part ORDER BY part");
$s->execute([<test_id>]);
foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r)
    echo "part".$r["part"]." ".$r["valid"]."/".$r["total"]."\n";
'
```

---

## Dọn dẹp (thủ công sau khi verify xong)

```bash
rm -rf "tests/[tên file đề].html" "tests/[tên]_files/"
rm -rf "answer/[tên file đáp án].html" "answer/[tên]_files/"
# giữ lại file JSON nếu cần debug, xóa sau khi chắc chắn ổn
```

---

## Trạng thái đề DECADE hiện tại

| Field | Giá trị |
|---|---|
| test_id | 8 |
| title | Đề thi thử TOEIC: Mã đề DECADE | Listening |
| is_active | 1 ✅ |
| Part 1 | 6 câu, 6/6 đáp án hợp lệ ✅ |
| Part 2 | 25 câu, 25/25 đáp án hợp lệ ✅ |
| Part 3 | 39 câu, 39/39 đáp án hợp lệ ✅ |
| Part 4 | 30 câu, 30/30 đáp án hợp lệ ✅ |
| Explanation | 0/100 - chưa có (trang web không cung cấp) |

> Explanation không có sẵn trong HTML đề thi cào về
> Nếu muốn thêm sau này phải nhập thủ công qua admin panel
