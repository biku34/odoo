import React, { useState, useEffect } from 'react';

export const ItemDetailPage = ({ itemId }) => {
  const [item, setItem] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchItemDetail = async () => {
      try {
        const response = await fetch(`http://localhost:5000/api/items/${itemId}`);
        if (!response.ok) throw new Error('Item not found');
        const data = await response.json();
        setItem(data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };
    fetchItemDetail();
  }, [itemId]);

  if (loading) return React.createElement('p', null, 'Loading...');
  if (error || !item) return React.createElement('p', { className: 'message' }, 'Item not found or error occurred.');

  return React.createElement('div', { className: 'item-detail-page' },
    React.createElement('div', { className: 'image-gallery' },
      item.images.map((img) => React.createElement('img', {
        key: img.id,
        src: img.image_url || 'https://via.placeholder.com/150',
        alt: item.title,
        className: img.is_primary ? 'primary-image' : 'gallery-image'
      }))
    ),
    React.createElement('div', { className: 'item-details' },
      React.createElement('h2', null, item.title),
      React.createElement('p', null, item.description || 'No description available'),
      React.createElement('div', { className: 'uploader-info' },
        React.createElement('p', null, `Uploaded by: ${item.uploader.name}`),
        item.uploader.profile_image && React.createElement('img', {
          src: item.uploader.profile_image,
          alt: 'Uploader Profile',
          className: 'profile-image'
        })
      ),
      React.createElement('p', null, `Status: ${item.status}`),
      React.createElement('div', { className: 'action-buttons' },
        item.status === 'available' && React.createElement(React.Fragment, null,
          React.createElement('button', {
            className: 'button',
            onClick: () => alert('Swap Request initiated')
          }, 'Swap Request'),
          React.createElement('button', {
            className: 'button',
            onClick: () => alert('Redeem via Points initiated')
          }, 'Redeem via Points')
        )
      )
    )
  );
};