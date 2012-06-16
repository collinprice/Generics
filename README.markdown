
Usage:

API is accessed through GET requests.

User Access:

* Create user
	* command: create
	* variables: username, password
	* return variable: user_id

* Authenticate user
	* command: authenticate
	* variables: username, password
	* return variables: user_id
	
* Set user variables
	* command: user_set
	* variables: userID, key=value, ...
	* return variables: [key=boolean]

* Get user variables
	* command: user_get
	* variables: userID, key
	* return variables: [key=value]

App Access:	
	
* Set app variables
	* command: app_set
	* variables: key=value, ...
	* return variables: [key=boolean]

* Get app variables
	* command: app_get
	* variables: key
	* return variables: [key=value]