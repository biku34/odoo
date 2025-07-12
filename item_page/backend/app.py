from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_cors import CORS
from models import db, User, Item, ItemImage
from werkzeug.security import generate_password_hash, check_password_hash

app = Flask(__name__)
CORS(app)

# MySQL Configuration
app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://root:your_password@localhost/rewear'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db.init_app(app)

# Create tables
with app.app_context():
    db.create_all()

# Login Route
@app.route('/api/login', methods=['POST'])
def login():
    data = request.get_json()
    email = data.get('username')
    password = data.get('password')

    if not email or not password:
        return jsonify({'message': 'Email and password are required'}), 400

    user = User.query.filter_by(email=email).first()
    if user and check_password_hash(user.password_hash, password):
        return jsonify({'message': 'Login successful', 'user_id': user.id})
    return jsonify({'message': 'Invalid credentials'}), 401

# Item Detail Route
@app.route('/api/items/<int:item_id>', methods=['GET'])
def get_item_detail(item_id):
    item = Item.query.get_or_404(item_id)
    user = User.query.get(item.user_id)
    images = ItemImage.query.filter_by(item_id=item_id).all()

    item_data = {
        'id': item.id,
        'title': item.title,
        'description': item.description,
        'category': item.category,
        'type': item.type,
        'size': item.size,
        'condition': item.condition,
        'status': item.status,
        'uploader': {'id': user.id, 'name': user.name, 'profile_image': user.profile_image},
        'images': [{'id': img.id, 'image_url': img.image_url, 'is_primary': img.is_primary} for img in images]
    }
    return jsonify(item_data)

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)