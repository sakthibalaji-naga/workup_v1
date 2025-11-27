# 🔧 Database Configuration for Docker Installation

## ⚠️ IMPORTANT: Use Correct Hostname

When installing your application in Docker, you **MUST** use the following database credentials:

---

## ✅ **Correct Database Settings for Installation**

### **Installation Form Values:**

| Field | Value | ⚠️ Important |
|-------|-------|-------------|
| **Hostname** | `mysql` | **NOT** `localhost` |
| **Database Name** | `workup_db` | |
| **Username** | `workup_user` | |
| **Password** | `workup_pass` | |

---

## ❌ **Common Mistake**

**DO NOT USE:**
- ❌ `localhost` as hostname
- ❌ `127.0.0.1` as hostname

**These will cause the error:**
```
Fatal error: Uncaught mysqli_sql_exception: No such file or directory
```

---

## 🔍 **Why `mysql` and not `localhost`?**

In Docker:
- **`localhost`** tries to use a Unix socket (`/var/run/mysqld/mysqld.sock`) which doesn't exist in the PHP container
- **`mysql`** uses the Docker network to connect to the MySQL container via TCP/IP
- Docker Compose creates a network where services can communicate using their service names

---

## 📝 **Installation Steps**

1. Navigate to: **http://localhost:8080/install/**
2. Complete the requirements and permissions steps
3. On the **Database Configuration** step, enter:
   - Hostname: `mysql`
   - Database Name: `workup_db`
   - Username: `workup_user`
   - Password: `workup_pass`
4. Continue with the installation

---

## 🗄️ **Database is Already Created**

The database `workup_db` with user `workup_user` is already created and ready to use. You just need to provide the correct connection details during installation.

---

## 🔧 **If You Need to Reset**

If the installation fails, you can reset the database:

```powershell
# Stop containers
docker-compose down

# Remove database volume (this deletes all data)
docker volume rm workup_mysql_data

# Start fresh
docker-compose up -d
```

---

## 📌 **Quick Reference**

**For Installation Form:**
```
Hostname: mysql
Database: workup_db
Username: workup_user
Password: workup_pass
```

**For phpMyAdmin (http://localhost:8081):**
```
Username: root
Password: root_password
```

---

## ✅ **Summary**

Use **`mysql`** as the hostname in your installation form, not `localhost`. This is the Docker service name that allows the PHP container to communicate with the MySQL container.

🚀 **Your database is ready and waiting for the correct connection!**
