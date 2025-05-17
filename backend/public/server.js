require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const path = require('path');
const cors = require('cors');

const app = express();

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

// Database connection
mongoose.connect(process.env.MONGO_URL, {
    useNewUrlParser: true,
    useUnifiedTopology: true
})
.then(() => console.log('Connected to MongoDB'))
.catch(err => console.error('MongoDB connection error:', err));

// Service Model
const serviceSchema = new mongoose.Schema({
    id: String,
    title: String,
    description: String,
    price: String,
    link: String,
    keywords: String,
    category: String,
    details: {
        summary: String,
        features: [String],
        fullDescription: String,
        packages: [{
            name: String,
            price: String,
            features: [String]
        }]
    }
});

const Service = mongoose.model('Service', serviceSchema);

// Routes
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'services.html'));
});

app.get('/services', (req, res) => {
    res.sendFile(path.join(__dirname, 'services.html'));
});

app.get('/api/services', async (req, res) => {
    try {
        const services = await Service.find({});
        res.json(services);
    } catch (err) {
        res.status(500).json({ message: err.message });
    }
});

app.post('/api/services/search', async (req, res) => {
    try {
        const searchQuery = req.body.query.toLowerCase();
        
        if (searchQuery.length < 2) {
            return res.status(400).json({ message: 'Введите минимум 2 символа для поиска' });
        }
        
        const foundServices = await Service.find({
            $or: [
                { title: { $regex: searchQuery, $options: 'i' } },
                { description: { $regex: searchQuery, $options: 'i' } },
                { keywords: { $regex: searchQuery, $options: 'i' } }
            ]
        });
        
        res.json(foundServices);
    } catch (err) {
        res.status(500).json({ message: err.message });
    }
});

// Error handling middleware
app.use((err, req, res, next) => {
    console.error(err.stack);
    res.status(500).send('Something broke!');
});

// Start server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});