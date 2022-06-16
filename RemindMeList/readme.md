## RemindMeList Webapp

#### Description & Usage
Simple webapplication to keep track of books, series and movies you want to remember to read or watch. To visit the index page the user has to be signed in. If no user is signed in, they will be redirected to the login page where they can log in to the webapp. If the user does not have an account yet, they can register at the register page. After logging in to the webapp the user can start entering media they don't want to forget about in the overview including some reminder text, for example: " recommended to me by Joe"  or " same director of Lord of the Rings". After completing a book, serie or movie the entry can easily be removed by clicking the checkmark next to the title.

#### Decisions & techniques
The goal of the app was to include as many of the thought techniques as possible and for them to be implemented well, rather than focus on the quantity of context. The website is written in PHP using HTML and CSS with the Bootstrap framework with a small amount of JS to utilize the flash messages well. The backend uses a MySQL database with PDO to save data and rapidly fetch them for the user when a page is loaded or a request is send to the server.

###### Final project for Bit-Academy Full-Stack Developer 
By Thomas Hendriks
