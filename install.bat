@echo off
echo [1/3] Membersihkan kontainer lama...
docker-compose down
echo [2/3] Memulai Docker Containers...
docker-compose up -d
echo [3/3] Selesai!
echo ---------------------------------------------------
echo Web: http://localhost:8080
echo MySQL (DBeaver): localhost:3306
echo ---------------------------------------------------
pause