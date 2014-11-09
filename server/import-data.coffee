#! /usr/bin/env coffee

app = require "./server"
fs = require 'fs'
dataSource = app.dataSources.mongoDB

for model in process.argv[2..] || ['business_enterprise',
                                   'business_local',
                                   'interest',
                                   'job',
                                   'mp',
                                   'question']
  if fs.existsSync "../data/#{model}.json"
    model_json = require("../data/#{model}.json")
    count = model_json.length
    console.log "Importing ", count, " records"
    dataSource.automigrate model, (err) ->
      model_json.forEach (r) ->
        app.models[model].create r, (err, result) ->
          unless err
            console.log "Record created:", result
            count--
            if count is 0
              console.log "done"
  else
    console.error "File does not exist: ../data/#{model}.json"
dataSource.disconnect()
process.exit()
