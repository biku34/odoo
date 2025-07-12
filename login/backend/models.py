from app import db

class ItemImages(db.Model):
    __tablename__ = 'item_images'
    id = db.Column(db.Integer, primary_key=True)
    item_id = db.Column(db.Integer, db.ForeignKey('items.id'), nullable=False)
    image_url = db.Column(db.Text, nullable=False)
    is_primary = db.Column(db.Boolean, default=False)

class Tags(db.Model):
    __tablename__ = 'tags'
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(50), unique=True, nullable=False)

class ItemTags(db.Model):
    __tablename__ = 'item_tags'
    item_id = db.Column(db.Integer, db.ForeignKey('items.id'), primary_key=True)
    tag_id = db.Column(db.Integer, db.ForeignKey('tags.id'), primary_key=True)

class Swaps(db.Model):
    __tablename__ = 'swaps'
    id = db.Column(db.Integer, primary_key=True)
    requester_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    responder_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    requester_item_id = db.Column(db.Integer, db.ForeignKey('items.id'))
    responder_item_id = db.Column(db.Integer, db.ForeignKey('items.id'))
    status = db.Column(db.String(20), default='pending')
    created_at = db.Column(db.DateTime, default=db.func.current_timestamp())
    updated_at = db.Column(db.DateTime, default=db.func.current_timestamp())

class Redemptions(db.Model):
    __tablename__ = 'redemptions'
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    item_id = db.Column(db.Integer, db.ForeignKey('items.id'))
    points_used = db.Column(db.Integer, nullable=False)
    status = db.Column(db.String(20), default='pending')
    created_at = db.Column(db.DateTime, default=db.func.current_timestamp())

class AdminActions(db.Model):
    __tablename__ = 'admin_actions'
    id = db.Column(db.Integer, primary_key=True)
    admin_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    item_id = db.Column(db.Integer, db.ForeignKey('items.id'))
    action = db.Column(db.String(50))
    reason = db.Column(db.Text)
    created_at = db.Column(db.DateTime, default=db.func.current_timestamp())

class ActivityLog(db.Model):
    __tablename__ = 'activity_log'
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    action_type = db.Column(db.String(50))
    reference_id = db.Column(db.Integer)
    message = db.Column(db.Text)
    created_at = db.Column(db.DateTime, default=db.func.current_timestamp())