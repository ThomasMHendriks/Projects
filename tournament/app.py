from flask import Flask

from flask import request, redirect, render_template, flash
from cs50 import SQL

from functions import pair_round

app = Flask(__name__)

# Appkey to run the app. Should be included in the environmental variables if the app was to be hosted
app.config.update(
    TESTING=True,
    SECRET_KEY='192b9bdd22ab9ed4d12e236c78afcb9a393ec15f71bbf5dc987d18273645bcbf'
)
app.config["TEMPLATES_AUTO_RELOAD"] = True

db = SQL("sqlite:///tournament.db")

@app.route("/", methods=["GET", "POST"])
def index():
    # Check if there is a ongoing tournament and immediately redirect to the new tournament page if not
    tournamentcheck = db.execute("SELECT * FROM tournament")
    if len(tournamentcheck) == 0:
        flash("No ongoing tournament, start a new tournament")
        return redirect("/new")

    if request.method == "POST":

        # Route to pair a new round
        if request.form.get("pair") == "yes":

            # Get specific info of the ongoing tournament
            tournament_info = db.execute("SELECT round_number,round_total FROM tournament")[0]

            # Get the current round info, add one so the logic wil work for the coming round
            current_round = tournament_info["round_number"] + 1
            max_rounds = tournament_info["round_total"]

            # Check if there are still outstanding results
            results_expected = db.execute("SELECT results_expected FROM tournament")[0]["results_expected"]
            if results_expected > 0:
                flash("Hand in all the expected results before the new round can be paired")
                return redirect("/")

            # Check if the last round is reached
            elif current_round > max_rounds:
                flash("All rounds have been played for the tournament")
                return redirect("/")

            # Call the pair_round function to create pairings for the round
            players = db.execute("SELECT * FROM players ORDER BY points DESC")
            pairings = pair_round(players)

            # prepare variable to insert the current round opponent resulting from the pairings into the DB
            opponent = "opp" + str(current_round)
            j = 0

            for x in range(0, len(pairings) - 1, 2):
                # Check for a bye and update the players table accordingly
                if pairings[x + 1] == "bye":
                    result = "result" + str(current_round)
                    old_points = db.execute("SELECT points FROM players WHERE name=?", pairings[x])[0]["points"]
                    new_points = old_points + 3
                    db.execute("UPDATE players SET ?=?, ?='won' WHERE name=?", opponent, pairings[x + 1], result, pairings[x])
                    db.execute("UPDATE players SET points=? WHERE name=?", new_points, pairings[x])

                # Insert the pairings into the players table
                else:
                    db.execute("UPDATE players SET ?=? WHERE name=?", opponent, pairings[x + 1], pairings[x])
                    db.execute("UPDATE players SET ?=? WHERE name=?", opponent, pairings[x], pairings[x + 1])
                    j += 1

            # Set the expected results required to keep track of when the round ends
            db.execute("UPDATE tournament SET results_expected=?, round_number=?", j, current_round)
            flash("Pairings posted")
            return redirect("/")

        # Route to process a result
        elif request.form.get("result") == "yes":
            winner = request.form.get("winner")

            # Check if a winner is selected in the form and get the player info from the DB
            if not winner:
                flash("Select a winner before clicking submit!")
                return redirect("/")
            winner_check = db.execute("SELECT * FROM players WHERE name=?", winner)[0]

            # Check if the winner name corresponds with one of the players in the tournament
            if len(winner_check) == 0:
                flash("Competitor not found, try again")
                return redirect("/")

            # Store the current round to insert into the correct data
            current_round = str(db.execute("SELECT round_number FROM tournament")[0]["round_number"])
            res_round = "result" + current_round

            # Check if competitor doesn't already have a result for the current round
            if winner_check[res_round]:
                flash("Result has already been reported")
                return redirect("/")

            # Find the loser of the round
            loser = winner_check["opp" + current_round]

            # Update the point total for the winner
            new_points = int(winner_check["points"]) + 3

            # Register the result of the round in the DB
            db.execute("UPDATE players SET ?='won' WHERE name=?", res_round, winner)
            db.execute("UPDATE players SET ?='lost' WHERE name=?", res_round, loser)
            db.execute("UPDATE players SET points = ? WHERE name=?", new_points, winner)

            # Update expected results page
            current = db.execute("SELECT results_expected FROM tournament")[0]["results_expected"]
            db.execute("UPDATE tournament SET results_expected=?", current - 1)
            flash("Result processed")
            return redirect("/")

        # Backup route in case a malicious user presents a custom post request
        else:
            flash("Something went wrong, try again")
            return redirect("/")

    # Route for GET request
    else:
        # Retrieve data from current round and tournament overview
        players = db.execute("SELECT * FROM players ORDER BY points DESC")
        tournament_info = db.execute("SELECT * FROM tournament")
        current_round = int(tournament_info[0]["round_number"])

        # Check whether the tournament is finished
        if tournament_info[0]["round_number"] == tournament_info[0]["round_total"] and tournament_info[0]["results_expected"] == 0:
            return render_template("finished.html", players=players)

        # Check if previous pairings exist
        if current_round > 0:
            competitors = []
            opponents = []
            results_in = []

            # Retrieve pairings of current round
            for pairing in players:

                # Collect the results that are already in
                if pairing["result" + str(current_round)]:
                    results_in.append(pairing["name"] + " " + pairing["result"+ str(current_round)] + " versus " + pairing["opp" + str(current_round)])

                # If the result is still out, collect the player and opponent
                elif pairing["name"] not in competitors and pairing["name"] not in opponents:
                        competitors.append(pairing["name"])
                        opponents.append(pairing["opp" + str(current_round)])

            return render_template("tournament.html", players=players, competitors=competitors, opponents=opponents, results_in=results_in)

        # If there are no previous pairings
        return render_template("index.html", players=players)

@app.route("/new", methods=["GET", "POST"])
def new():
    if request.method == "GET":

        # Check if there is a current ongoing tournament
        tournamentcheck = db.execute("SELECT * FROM tournament")
        if len(tournamentcheck) > 0:
            flash("Finish or exit the tournament in progress.")
            return redirect("/")

        # Render page including the current registered players
        players = db.execute("SELECT name FROM players")
        return render_template("new.html", players=players)

    else:
        # Check if there is an active tournament with a different message for the post request
        tournamentcheck = db.execute("SELECT * FROM tournament")
        if len(tournamentcheck) > 0:
            flash("Can't add or remove players when a tournament is in progress")
            return redirect("/")

        # Check whether a player is to be added or removed
        if request.form.get('operation'):
            operation = request.form.get('operation')
            player_name = request.form.get('playerName')
            if isinstance(player_name, str):
                player_name = player_name.strip()

            # Validate inputs
            if operation != "add" and operation != "remove":
                flash("Invalid operation")
                return redirect("/new")

            # Check if a name is provided and isn't too long
            if player_name == "":
                flash("Fill in a name")
                return redirect("/new")
            elif len(player_name) > 30:
                flash('Name too long, fill in a shorter name')
                return redirect("/new")

            # check in the DB if there already is a player present with the chosen name
            current = db.execute("SELECT name FROM players WHERE name=?", player_name)

            # Add a player
            if operation == "add":

                # Check whether a player with that name is already in the tournament
                if len(current) > 0:
                    flash("Player already registered")
                    return redirect("/new")

                # Add player to the database
                db.execute("INSERT INTO players (name, points) VALUES (?, 0)", player_name)
                flash("Player succesfully added")
                return redirect("/new")

            # Remove a player path
            else:
                # Check if player exists in DB
                if len(current) == 0:
                    flash("Player is not currently registered")
                    return redirect("/new")

                # remove the row with the to be removed player from the DB
                db.execute("DELETE FROM players WHERE name=?", player_name)
                flash("Player succesfully removed")
                return redirect("/new")


        # Check there is a request to start a tournament
        elif request.form.get('numberRounds'):
            number_of_rounds = request.form.get('numberRounds')

            # Check for valid number of desired rounds
            if number_of_rounds.isdigit() == False:
                flash("Choose a positive number for number of rounds")
                return redirect("/new")

            # Check for max number of rounds
            if int(number_of_rounds) >= 5:
                flash("Max number of rounds is 4")
                return redirect("/new")

            # Check for a minumum of 4 players
            participants = db.execute("SELECT name FROM players")
            if len(participants) < 4:
                flash("Minumum of 4 players needed to start a tournament")
                return redirect("/new")

            # Start the tournament with the currently registered players
            db.execute("INSERT INTO tournament (tournament_name, round_total, round_number, results_expected) VALUES ('current', ?, 0, 0)",
                        number_of_rounds)
            flash("Tournament started")
            return redirect("/")

        # Safety else statement to return to the same page with an error message in case of custom requests
        else:
            flash("Something went wrong, try again")
            return redirect("/new")


@app.route("/stop", methods=["GET", "POST"])
def stop():
    if request.method == "GET":
        return render_template("stop.html")

    else:
        # Validate the request input
        if request.form.get("stopTournament") == "yes":
            db.execute("DELETE FROM tournament")
            db.execute("DELETE FROM players")
            flash("Current tournament ended, start a new tournament")
            return redirect("/new")

        # Redirect to frontpage in case input was not yes
        else:
            flash("Something went wrong, try again")
            return redirect("/")