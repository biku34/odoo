from flask import Flask, request, jsonify
from flask_cors import CORS
from db_config import get_db_connection
from werkzeug.security import generate_password_hash
import psycopg2
import traceback

app = Flask(__name__)
CORS(app)

@app.route('/register', methods=['POST'])
def register():
    data = request.json
    name = data.get('name')
    email = data.get('email')
    password = data.get('password')

    if not name or not email or not password:
        return jsonify({'message': 'Missing fields'}), 400

    hashed_pw = generate_password_hash(password)

    try:
        conn = get_db_connection()
        cur = conn.cursor()
        cur.execute("""
            INSERT INTO users (name, email, password_hash)
            VALUES (%s, %s, %s)
        """, (name, email, hashed_pw))
        conn.commit()
        return jsonify({'message': 'Registration successful'}), 200

    except psycopg2.Error as sql_err:
        traceback.print_exc()  # 🔍 Show traceback in terminal
        conn.rollback()
        return jsonify({'message': f'Database error: {sql_err.pgerror}'}), 500

    except Exception as e:
        traceback.print_exc()
        return jsonify({'message': 'Unexpected server error'}), 500

    finally:
        cur.close()
        conn.close()

if __name__ == '__main__':
    app.run(debug=True)




