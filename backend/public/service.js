const mongoose = require('mongoose');

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

module.exports = mongoose.model('Service', serviceSchema);