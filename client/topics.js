var jf = require('jsonfile')
var util = require('util')
var Client = require('node-rest-client').Client;
var request = new Client();


var file = 'questions_topics_dump.json'
jf.readFile(file, function(error, data) {
    if(!error) {
        console.log(data);
    }
    else {
        console.log(error);
    }
})

/*request.get('https://api.opencorporates.com/v0.3/companies/search?q=barclays&order=score&jurisdiction_code=gb', function(data, response) {
    // parsed response body as js object
    console.log(data.results.companies[0].company);
    // raw response
    //console.log(response);
});*/

//console.log(topics);