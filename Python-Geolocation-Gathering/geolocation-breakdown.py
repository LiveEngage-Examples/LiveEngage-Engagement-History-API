import json
import requests
from requests_oauthlib import OAuth1
from requests_oauthlib import OAuth1Session
import time
import csv
from collections import Counter

"""
Install dependencies: pip install -r requirements.txt

To run this file, you will need to update the following variables with your account information:
- accountNum, consumer_key, consumer_secret, access_token, access_token_secret
You will also need to update the start.to and start.from values in the body for the API request.
"""

#########################
### BoilerPlate Code ####
#########################

# Grab the time when the script starts.
start_time_epoch = time.time()

# LiveEngage Account Number
accountNum = 'xx'

# Get these from the LiveEngage API management area
consumer_key = 'xx'
consumer_secret = 'xx'
access_token = 'xx'
access_token_secret = 'xx'

oauth = OAuth1(consumer_key,
			   client_secret=consumer_secret,
			   resource_owner_key=access_token,
			   resource_owner_secret=access_token_secret,
			   signature_method='HMAC-SHA1',
			   signature_type='auth_header')

client = requests.session()
postheader = {'content-type': 'application/json'}

# Skills IDs that we care about
MY_SKILLS = ['134515514',
'xx',
'xx',
'xx',]


# TODO gather skills and put in the body
# Customize the body for what you want
body={
	'interactive':'true',
	'ended':'true',
	'start':{
		# http://www.epochconverter.com/ - grab the millisecond version
		'from':'1521086401000',
		'to':'1523764801000'
	},
	'skillIds': MY_SKILLS,
}

domainReq = requests.get('https://api.liveperson.net/api/account/' + accountNum + '/service/engHistDomain/baseURI.json?version=1.0')
if not domainReq.ok:
	print('There was an issue with your Eng. History base URI')
domain = domainReq.json()['baseURI']
engHistoryURI = 'https://' + domain + '/interaction_history/api/account/' + accountNum + '/interactions/search?'

count = 1 # Count is the total num of records in the response
offset = 0 # offset is to keep track of the amount difference between what we've pulled so far and what the total is.
limit = 100 # max chats to be recieved in one response
numRecords = 0

#########################
###     Grab Data    ####
#########################
countries = Counter()
while(offset <= count): # Grab the data incrementally because can only pull 100 at a time.

	# Complete the Requests.session POST
	params={'offset':offset, 'limit':limit, 'start':'des'} # Prob shouldn't change offset and limit
	engHistoryResponse = client.post(url=engHistoryURI, headers=postheader, data=json.dumps(body), auth=oauth, params=params)
	if not engHistoryResponse.ok:
		print(engHistoryResponse.status_code)
	engHistoryResults = engHistoryResponse.json()

	for record in engHistoryResults['interactionHistoryRecords']:
		numRecords += 1
		try:
			country = record['visitorInfo']['country']
			if not country.strip():
				countries['N/A'] += 1
			else:
				countries[record['visitorInfo']['country']] += 1
		except KeyError:
			countries['N/A'] += 1
	# Update count, offset
	count = engHistoryResults['_metadata']['count']
	offset += limit
	# print the status of the aggregation
	print(str(offset) + "<=" + str(count))

print("num records processed = " + str(numRecords))

####################
### Output Data ####
####################

# Construct our output file name
outfile = 'LiveEngage_geolocation_output_' + time.strftime("%Y%m%d-%H%M%S") + '.csv'

with open(outfile,'w', newline='') as csvfile:
	fieldnames=['country','count']
	writer=csv.writer(csvfile)
	writer.writerow(fieldnames)
	for key, value in countries.items():
		row = [key, value]
		writer.writerow(row)

print("Output file: " + outfile)
print("--- %s seconds to complete script." % (time.time() - start_time_epoch))
