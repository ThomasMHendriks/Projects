# ThomasMHendriks
=======
# Tournament

#### Video Demo: https://youtu.be/Jd9QS1xyEag

#### Description
A tournament webapp to register players, start a tournament, pair players in a [swiss-system](https://en.wikipedia.org/wiki/Swiss-system_tournament) manner and keep track of the results

#### Techniques
Tournament is a webapp build in Python using the Flask framework and Jinja Templating. The application uses a database throught SQLITE3 to save tournament information and player information. The website is stylized through the bootstrap framework with custom CSS to style to make it look nice (in my opinion :)).

#### Usage & routes/pages
App has an intended use patern, and will redirect the user to follow that patern while explaining why using flash messages. The patern is intended to first create a tournament, then play out the tournament, then abort the tournament to be able to start a new one.

###### New tournament route:
When first visiting the website the app checks the database if there is an ongoing tournament and displays that information if there is. Otherwise the user will be redirected to the new tournament route. At the new tournament page the user can register people into the tournament. The user can then start the tournament whenever all players are registered with a desired number of rounds between 1 and 5. The user will then be send to the default route, which is where the tournament standings will be displayed.

###### Ongoing tournament route:
At the tournament page, the user can let the rounds get paired. The application pairs randomly by current points and tries to correct for previous opponents. The latter can be impossible, for example in a 4 round tournament with only 4 players. In that case the app will allow for repairings to the same opponent. When a tournament is started with an odd number of players, one of the players (tried from the bottom of the standings) will be awarded a bye each round. The players will be presented with the ongoing matches and can select the winner of those matches to enter the match result in the tournament. When all results for the round are in, the user will be prompted to pair the next round untill all the planned rounds are finished.

###### Stop tournament route:
At this point the final standings are displayed. There will be an option available to abort the current tournament so a new tournament can be started. This option can always be selected from the tournament control panel in case the user wants to start a new tournament before the current one is finished. The user will be redirected to the stop route, at which the user will be warned about deleting the current tournament, after selecting that option, a the user will be redirected to the new tournament page.

#### Functions
To pair the rounds for the players 2 custom functions are used. The first one divides all the players into groups based on points, shuffles those groups and makes an array of pairings. During this the function checks for odd number of players in a group to pass a played to the next group so a group always has an even number of players to pair. If an odd number of players is registered to the tournament, the lowest paired player, with usually the least amount of points will be awarded the bye. The pair_round function then calls the pairing_check function.

The pairing_check funtion checks whether a player is paired versus a player they have already faced and tries 5 times to switch players around to prevent this. If this doesn't work it returns the original pairings hereby accepting the re-pairing.

#### Database
I chose a simple database schema that only keeps track of one tournament and its registered players at a time. This is because the app is supposed to support an easy manner of running a tournament and be done with it after it is done. To start a new tournament, all previous database entries have to be deleted. The database contains 2 tables, one with the info on the current tournament, and one of the players which also contains the round results. By using the database in this way, multiple participating players could visit the app if it were hosted to hand in their own results without being reliant on local storage.

#### HTML templates used
- The layout template includes the general layout, a nav bar, mobile device support thanks to Bootstrap as well as linking to the custom stylesheet for each page
- The new template shows the tournament creation page where players can be enrolled
- The index template is used to render the page that shows the tournament before the first round starts
- The tournament template shows the standings, ongoing matches, allows the user to hand in results and shows the result of completed matches
- the stop page shows the page where a user can remove the ongoing tournament and all users

#### CSS
Bootstrap is used to create clean input fields, forms, buttons etc. as well as a nav bar.
To create a easy on the eye look, custom css styling is included in the static folder.

###### Created by
Thomas Hendriks for CS50 as a final project for CS50
