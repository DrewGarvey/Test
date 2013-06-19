#BSD Votes Plugin

##Display voting statistics from a signup form

###{exp:bsd_votes:ids}: Returns a pipe-separated list of entry_ids, ordered by number of votes received.

Sample tag: {exp:bsd_votes:ids slug="voting-form" limit="5" parse="inward"}

####Required parameter:

- "slug" : the slug of the voting form
- "parse" : "inward" : must be set in order for this to behave as expected

    
####Optional Parameter:

- "limit" : limit the number of entries returned. Default value: 10

### {exp:bsd_votes:votes} (was {exp:bsd_votes:score}) Returns the number of votes received for a particular entry_id.

Sample Tag: {exp:bsd_votes:votes entry_id="430" slug="voting-form"}

####Required parameters:

- "entry_id" : the entry id to return votes for
- "slug" - the slug of the voting form

###{exp:bsd_votes:rank} Returns the ranking for an entry, specified by entry_id.

Sample Tag: {exp:bsd_votes:rank entry_id="430"}

####Required parameters: 

- "entry_id" : the entry_id for which to return a ranking
- "slug" : the slug of the signup form

####Optional parameter:

- "limit" : limit the number of entries searched. Default value: 10.