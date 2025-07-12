import React, { useState } from 'react';

const App = () => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

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
        // Handle successful login (e.g., redirect or store user_id)
        console.log('User ID:', data.user_id);
      }
    } catch (error) {
      setMessage('An error occurred. Please try again.');
    }
  };

  return React.createElement('div', { className: 'login-page' },
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
      React.createElement('p', { className: 'message' }, message)
    )
  );
};

export default App;