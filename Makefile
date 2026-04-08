.PHONY: all up down frontend backend db logs clean build query

# lệnh chạy toàn bộ khi chỉ gõ `make`
all: up

# khởi động cả 3 cụm container (dạng background)
up:
	docker compose up -d

# tắt mọi container đang hoạt động
down:
	docker compose down

# restart và build lại (đặc biệt khi bạn chỉnh sửa file Dockerfile)
build:
	docker compose up -d --build

# chạy test riêng lẻ từng phân nhánh project
frontend:
	docker compose up -d frontend

backend:
	docker compose up -d backend

db:
	docker compose up -d db

# chạy thẳng vào prephub + đổi tên [mysql] trong terminal thành [prephub luôn]
query:
	docker exec -it prephub_db mysql -u root -p --prompt="[\d]> " prephub

# xem màn hình console log realtime của toàn cục
logs:
	docker compose logs -f

# xem log cá nhân từng phân khu nhỏ để tiện debug
logs-fe:
	docker compose logs -f frontend

logs-be:
	docker compose logs -f backend

logs-db:
	docker compose logs -f db

# dọn sạch hệ thống tắt mọi container (cảnh báo: sẽ drop toàn bộ data tables trong mysql)
clean:
	docker compose down -v
