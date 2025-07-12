
from db_config import get_db_connection

def init_db():
    conn = get_db_connection()
    cur = conn.cursor()

    with open('D:\odoo hackathon\sql\create_users.sql', 'r') as f:  # Adjust path if needed
        cur.execute(f.read())

    conn.commit()
    cur.close()
    conn.close()
    print("✅ Tables created successfully.")

if __name__ == '__main__':
    init_db()
