import React, { useState } from 'react';
import { ItemDetailPage } from './ItemDetailPage.js';

const App = () => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');
  const [showItemDetail, setShowItemDetail] = useState(false);
  const [itemId, setItemId] = useState(1); // Default to item ID 1

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch('http://localhost:5000/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password }),
      });
      const data = await response.json();
      setMessage(data.message);
      if (response.status === 200) {
        console.log('User ID:', data.user_id);
        // Optionally navigate to item detail after login
        // setShowItemDetail(true);
      }
    } catch (error) {
      setMessage('An error occurred. Please try again.');
    }
  };

  const handleViewItem = (id) => {
    setItemId(id);
    setShowItemDetail(true);
  };

  return React.createElement('div', null,
    !showItemDetail ? React.createElement('div', { className: 'login-page' },
      React.createElement('div', { className: 'login-container' },
        React.createElement('div', { className: 'logo' }),
        React.createElement('h2', null, 'Login'),
        React.createElement('form', { onSubmit: handleSubmit },
          React.createElement('div', { className: 'input-group' },
            React.createElement('input', {
              type: 'text',
              id: 'username',
              value: username,
              onChange: (e) => setUsername(e.target.value),
              placeholder: 'Username',
              required: true
            })
          ),
          React.createElement('div', { className: 'input-group' },
            React.createElement('input', {
              type: 'password',
              id: 'password',
              value: password,
              onChange: (e) => setPassword(e.target.value),
              placeholder: 'Password',
              required: true
            })
          ),
          React.createElement('button', { type: 'submit', className: 'button' }, 'Login')
        ),
        React.createElement('p', { className: 'message' }, message),
        React.createElement('button', {
          className: 'button',
          onClick: () => handleViewItem(1),
          style: { marginTop: '10px' }
        }, 'View Item Detail')
      )
    ) : React.createElement(ItemDetailPage, { itemId: itemId })
  );
};

export default App;