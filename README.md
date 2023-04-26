# techuplab_assignment
Steps to install or initialize the project


#Step1:- Open Config.php and update the db and url parameters


for e.g. 
            "db_host" => "localhost",
            "db_name" => "db_assignment",
            "db_username" => "root",
            "db_password" => "",
            "url" => "http://localhost/assignment/" //this is the rest api base url.
            
            
---------------------------------------------------------------------------------------
#Step 2:- 

Open the POSTMAN and run the following url 

Method:- GET

URL:- http://localhost/assignment/migrations.php

After the migrations are created there is one user is registered with the applications

----------------------------------------------------------------------------------------
Step 3:- 

Get the JWT Token 

Login using the following url and generate the JWT Token

Method:- POST
URL:- http://localhost/assignment/api.php?action=login

Body :-
{
    "email": "sud@test.com",
    "password": "test@123"
}

OUTPUT:-
{
    "success": 1,
    "message": "You have successfully logged in.",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2Fzc2lnbm1lbnRcLyIsImF1ZCI6Imh0dHA6XC9cL2xvY2FsaG9zdFwvYXNzaWdubWVudFwvIiwiaWF0IjoxNjgyNTEwNDAwLCJleHAiOjE2ODI1MTQwMDAsImRhdGEiOnsidXNlcl9pZCI6IjEifX0.Ho6dxQRfCABsqqvM6-XO7K-8MOz3oDmqmuMYzL6TT7M"
}

Copy the token [Valid for 1 hour, we need to re-login after 1 hour to generate new token]


-----------------------------------------------------------------------------------------------------


Step 4:- After getting the JWT token we can hit various endpoints and get the desired results


-----------------------------------------------------------------------------------------------------


Register new user:-

URL:- http://localhost/assignment/api.php?action=register

Method:- POST

Body:- 

{
    "name": "sud",
    "email": "sud@new.com",
    "password": "test@123"
}


----------------------------------------------------------------------------------------


Create Tasks and Notes

URL:- http://localhost/assignment/api.php?action=create_task

Authorization :-  JWT bearer --- value:- copy and paste the token which is created from login process

Method:- POST

form-data:- 

subject:Example Task
description:Description of example task
start_date:2023-05-01
due_date:2023-05-10
status:New
priority:High
notes: [{"subject": "test test test trst","note":"Hello this is test note"},{"subject": "Example note 2","note":"The world is not enough."}]
attachments[]: FILE TYPE


---------------------------------------------------------------------------------------------

Retrieve all the tasks with Notes

URL:- http://localhost/assignment/api.php?action=get_task&status=incomplete&due_date=30-04-2023&priority=medium&notes=true

Authorization :-  JWT bearer --- value:- copy and paste the token which is created from login process

Method:- GET

Params:-
action:get_task
status:incomplete
due_date:30-04-2023
priority:medium
notes:true


-------------------------------------------------------------------------------------------------

