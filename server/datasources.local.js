var mongoUri = process.env.MONGO_URI || process.env.MONGOLAB_URI || process.env.MONGOHQ_URL || 'mongodb://localhost/motiv';

module.exports = {
  mongoDB: {
    connector: "loopback-connector-mongodb", 
    url: mongoUri
  }
}; 
