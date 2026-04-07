# Hướng dẫn làm việc với database
- Dùng MySQL 8.0
> [!IMPORTANT]
> Khi sử dụng docker, database mỗi người là độc lập trên mỗi masy, vì vậy data cũng độc lập  
> Sẽ có 1 database là Master, nó là database gốc cũng như là database sử dụng chính của web  
> Vậy nếu muốn update data với Master, hoặc ngược lại tới database local thì phải tự export rồi import  
> Sử dụng docker để hạn chế vấn đề lỡ 1 người có nhầm lẫn trong quá trình làm việc thì cả team không bị thiệt theo


### Khởi tạo database
- Chạy `make db` để pull MySQl 8.0 docker image về

### Kết nối database
> [!NOTE]
> Một lưu ý nhỏ là docker đã tự động detect file `.env` rồi, mỗi database có thể có thông tin khác nhau nhưng vì dùng chung schema nên là về cơ bản database mỗi máy là như nhau khi dev  
> Mỗi database của mỗi máy là một database test, có thể làm gì làm, chủ yếu vẫn làm việc qua data của Master + query schema  
> Chúng ta chỉ quan tâm tới data chính trên Master thôi

Có 2 cách:
#### Cách 1. Dùng terminal (với docker):
- Vui lòng sử dụng thông tin kết nối database có trong `.env`
- Dùng lệnh `make db`, xong nhập mật khẩu database
- Rồi nhập query mình muốn
#### Cách 2: Dùng editor:
- Dùng bất cứ database editor nào cũng được
- Vui lòng sử dụng thông tin kết nối database có trong `.env`
- Dùng thông tin đó để kết nối trong phần Connect với URL hoặc nhập từng thông tin
- Rồi nhập query mình muốn
#### Sau khi kết nối xong nó sẽ ra như này:
```bash
docker exec -it prephub_db mysql -u root -p --prompt="[\d]> " prephub
Enter password:
Reading table information for completion of table and column names
You can turn off this feature to get a quicker startup with -A

Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 11
Server version: 8.0.45 MySQL Community Server - GPL

Copyright (c) 2000, 2026, Oracle and/or its affiliates.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

[prephub]> 
```

### Khi muốn chỉnh sửa database
Ví dụ có 2 người A và B:
- Khi A muốn thêm 1 bảng mới (ví dụ bảng chat):
- A viết thêm CREATE TABLE chat ... vào cuối file [`server/db/schema.sql`](../server/db/schema.sql)
- A chạy lệnh đó trên database máy bạn để làm việc
- Sau khi xong xuôi, A git commit và push file schema.sql đó lên bằng Pull Request
<br>

- Khi B git pull về:
- Thấy file schema.sql có thay đổi.
- Dùng editor hoặc terminal và chạy đoạn code mới đó vào database trên máy B là xong

### Một lưu ý nhỏ khác về database Master
- Database master sẽ được deploy trên 1 service riêng, khi đó ta sẽ dùng URL của nó để kết nối
- Sử dụng Railway hoặc Render
