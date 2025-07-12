Problem Statement - 3 - ReWear – Community Clothing Exchange
Team Member Name 
-------------------
Bikram Sadhukhan - bikramsadhukhan505@gmail.com
Hetal Sapkale - hetal.dfis242622@nfsu.ac.in 
Jash Parekh - parekhjash1@gmail.com 

# 👕 ReWear – A Smart Clothing Exchange Platform

ReWear is a web-based platform that encourages sustainable fashion by enabling users to swap, donate, or redeem clothing items. Designed with a focus on usability, modular backend, and clean interface, ReWear ensures secure data handling, real-time interaction, and visual insights into platform activity.

## 🔗 Live Demo
🌐 [Coming Soon or add your hosted URL]

## 📸 Preview

![Landing Page](screenshots/landing.png)
![Admin Panel](screenshots/admin_panel.png)
![Item Detail](screenshots/item_detail.png)

---

## 🚀 Key Features

- 🛍️ **Browse & Add Items** – Users can list their clothing items with images and descriptions.
- 🔐 **User Auth & Moderation** – Secure login system with admin approval workflows.
- 📊 **Data Visualization** – Track user activity and item stats with Chart.js and Matplotlib.
- 🤖 **Interactive Chatbot** – AI bot estimates points for items using Hugging Face API.
- ☁️ **Cloud Storage** – Images and data hosted in the cloud for fast access & scalability.

---

## 🧠 Team Contributions

### 👩‍💻 Hetal Sapkale
- Built a secure user registration system with PostgreSQL.
- Implemented an admin panel using Flask for user/item moderation.
- Integrated data visualization with Chart.js and Matplotlib.
- Structured backend with modular Python and SQL integration.

### 👨‍💻 Jash Parekh
- Developed a Flask backend with MySQL for users, items, and images.
- Created a login system and item detail page using React.
- Designed UI with vibrant color scheme `#7c3aed` & `#a855f7`.
- Built a standalone chatbot using Hugging Face API and JavaScript.

### 👨‍💻 Bikram Sadhukhan
- Designed landing page and item upload form using HTML, CSS, and PHP.
- Implemented SQL database for item and user data.
- Ensured security with input sanitization and server-side validation.
- Connected cloud storage for image and DB access.
- Created a lightweight admin interface for item approvals.

---

## 🛠️ Tech Stack

**Frontend**  
`HTML` `CSS` `JavaScript` `React.js`

**Backend**  
`Python (Flask)` `PHP` `SQL` `PostgreSQL` `MySQL`

**Data Visualization**  
`Chart.js` `Matplotlib`

**AI & APIs**  
`Hugging Face API` `xAI (planned)`

**Storage & Hosting**  
`Cloudinary / Firebase / Supabase (mention your exact stack)`

---

## 🗂️ Database Schema Overview

```sql
Users(user_id, name, email, password_hash)
Items(item_id, title, description, image_url, uploaded_by, status)
SwapRequests(request_id, item_id, from_user, to_user, status)
