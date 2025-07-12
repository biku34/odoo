const chatMessages = document.getElementById('chatMessages');
const userInput = document.getElementById('userInput');
let conversationContext = ''; // To maintain context across messages

function addMessage(message, isUser = false) {
  const messageDiv = document.createElement('div');
  messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
  messageDiv.textContent = message;
  chatMessages.appendChild(messageDiv);
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

function estimatePoints(action, condition, category, type) {
  let basePoints = 0;
  const conditions = { 'like new': 100, 'good': 70, 'fair': 40, 'poor': 20 };
  basePoints = conditions[condition.toLowerCase()] || 50; // Default to 50 if condition unknown

  let categoryMultiplier = 1;
  if (category.toLowerCase() === 'pants') categoryMultiplier = 1.1;
  else if (category.toLowerCase() === 'dresses') categoryMultiplier = 1.2;
  else if (category.toLowerCase() === 'accessories') categoryMultiplier = 0.9;
  else if (category.toLowerCase() === 'tshirt') categoryMultiplier = 1; // Tshirt as base category

  let typeAdjustment = 1;
  if (type.toLowerCase() === 'kids') typeAdjustment = 0.8;

  const adjustedPoints = basePoints * categoryMultiplier * typeAdjustment;

  return action.toLowerCase() === 'sell' ? Math.round(adjustedPoints) : Math.round(adjustedPoints * 1.1);
}

function parseClothingDetails(message) {
  const lowerMessage = message.toLowerCase();
  let action = 'sell';
  let condition = 'good';
  let category = 'tshirt'; // Default to tshirt to match your example
  let type = 'men'; // Default to men to match your example

  if (lowerMessage.includes('buy')) action = 'buy';
  if (lowerMessage.includes('sell')) action = 'sell';
  if (lowerMessage.includes('like new')) condition = 'like new';
  else if (lowerMessage.includes('good')) condition = 'good';
  else if (lowerMessage.includes('fair')) condition = 'fair';
  else if (lowerMessage.includes('poor')) condition = 'poor';
  if (lowerMessage.includes('tshirt') || lowerMessage.includes('tshirts')) category = 'tshirt';
  else if (lowerMessage.includes('pant') || lowerMessage.includes('pants')) category = 'pants';
  else if (lowerMessage.includes('dress') || lowerMessage.includes('dresses')) category = 'dresses';
  else if (lowerMessage.includes('accessory') || lowerMessage.includes('accessories')) category = 'accessories';
  if (lowerMessage.includes('men')) type = 'men';
  else if (lowerMessage.includes('women')) type = 'women';
  else if (lowerMessage.includes('kids')) type = 'kids';
  // Handle the "which is" and "category" structure
  const whichIndex = lowerMessage.indexOf('which is');
  if (whichIndex !== -1) {
    const conditionPart = lowerMessage.substring(whichIndex + 8).split(' and ')[0].trim();
    if (conditionPart.includes('like new')) condition = 'like new';
    else if (conditionPart.includes('good')) condition = 'good';
    else if (conditionPart.includes('fair')) condition = 'fair';
    else if (conditionPart.includes('poor')) condition = 'poor';
  }
  const categoryIndex = lowerMessage.indexOf('category');
  if (categoryIndex !== -1) {
    const categoryPart = lowerMessage.substring(categoryIndex + 8).trim().split(' ')[0];
    if (categoryPart === 'men' || categoryPart === 'women' || categoryPart === 'kids') type = categoryPart;
    else if (categoryPart) category = categoryPart;
  }

  return { action, condition, category, type };
}

function sendMessage() {
  const message = userInput.value.trim();
  if (!message) return;

  addMessage(message, true);
  userInput.value = '';

  conversationContext += ` User: ${message}\n`;

  fetch('https://api-inference.huggingface.co/models/facebook/blenderbot-400M-distill', {
    method: 'POST',
    headers: {
      'Authorization': 'enter api key here', // Replace with your Hugging Face API token
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ inputs: `Context: ${conversationContext}. Extract clothing details from the latest user message: ${message}. Return action (sell/buy), condition (like new/good/fair/poor), category (tshirt/pants/dresses/accessories), type (men/women/kids). If details are missing, ask a follow-up question.` })
  })
  .then(response => response.json())
  .then(data => {
    let details;
    let responseText = '';
    if (data && data.generated_text) {
      const parsed = data.generated_text.match(/action: (\w+), condition: (\w+), category: (\w+), type: (\w+)/i);
      if (parsed) {
        details = { action: parsed[1], condition: parsed[2], category: parsed[3], type: parsed[4] };
        const points = estimatePoints(details.action, details.condition, details.category, details.type);
        responseText = `Great choice! For a ${details.condition} ${details.category} (${details.type}): 
          ${details.action === 'sell' ? 'You will gain' : 'You will need to pay'} ${points} points. Anything else I can help with?`;
      } else if (data.generated_text.includes('follow-up')) {
        responseText = data.generated_text.replace('Context: ...', '').trim();
      }
    }
    if (!responseText) {
      details = parseClothingDetails(message);
      const points = estimatePoints(details.action, details.condition, details.category, details.type);
      responseText = `Great choice! For a ${details.condition} ${details.category} (${details.type}): 
        ${details.action === 'sell' ? 'You will gain' : 'You will need to pay'} ${points} points. Anything else I can help with?`;
    }
    addMessage(responseText);
    conversationContext += ` Bot: ${responseText}\n`;
  })
  .catch(error => {
    const details = parseClothingDetails(message);
    const points = estimatePoints(details.action, details.condition, details.category, details.type);
    const responseText = `Great choice! For a ${details.condition} ${details.category} (${details.type}): 
      ${details.action === 'sell' ? 'You will gain' : 'You will need to pay'} ${points} points. Anything else I can help with?`;
    addMessage(responseText);
    conversationContext += ` Bot: ${responseText}\n`;
    console.error('API Error:', error);
  });
}

// Allow pressing Enter to send message
userInput.addEventListener('keypress', (e) => {
  if (e.key === 'Enter') sendMessage();
});

// Initial welcome message with interactive tone
addMessage('Hey there! I’m your ReWear Points Chatbot. I can estimate points for selling or buying clothes. Try something like "I want to buy a tshirt which is like new and men category," and I’ll calculate it for you! Feel free to ask anything!');