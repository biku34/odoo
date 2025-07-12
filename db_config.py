import psycopg2  # ✅ Must be imported to use psycopg2.connect

def get_db_connection():
    return psycopg2.connect(
        host="localhost",       # Or your server IP if remote
        port=5432,              # Default port (adjust if different)
        database="rewear_db",   # Your DB name from pgAdmin
        user="postgres",        # Your PostgreSQL username
        password="post"  # Your password from install
    )
