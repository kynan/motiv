var app = require('./server');
var dataSource = app.dataSources.mongoDB;
var MP = app.models.mp;
var mps = require('../data/mp.json');

var count = mps.length;
console.log('Importing ', count, ' records');
dataSource.automigrate('mp', function (err) {
  mps.forEach(function(mp) {
    MP.create(mp, function(err, result) {
      if(!err) {
        console.log('Record created:', result);
        count--;
        if(count === 0) {
          console.log('done');
          dataSource.disconnect();
        }
      }
    });
  });
});


