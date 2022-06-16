import random

def pair_round(players):
    '''
    Pairs the players in the tournament based on points.
    Corrects for an uneven amount of players and awards a bye to one of the lowest point players.
    Returns the round pairings after calling the pair check function that checks for previous opponents
    '''
    # Initialize variables
    groups = {}
    pairings = []
    temp = None

    # Divide the players into groups based on points
    for player in players:
        if player["points"] not in groups:
            groups[player["points"]] = [player["name"]]
        else:
            groups[player["points"]].append(player["name"])

    # Loop over groups to pair each group
    for group in groups:
        just_added = False

        # check if a players is stored in temp and append to the new group if so
        if temp != None:
            groups[group].append(temp)
            temp = None
            just_added = True

        # if a group contains an uneven number of players, move one player on to the next group
        if len(groups[group]) % 2 != 0:

            # Pick a random person to drop down to the next group.
            if len(groups[group]) == 1:
                temp = groups[group].pop()

            # Exclude a player just added to the drop down options
            elif just_added == True:
                random_in_group = random.randrange(len(groups[group]) - 1)
                temp = groups[group].pop(random_in_group)

            else:
                random_in_group = random.randrange(len(groups[group]))
                temp = groups[group].pop(random_in_group)

        # Shuffle the group to creature random pairings
        random.shuffle(groups[group])

        # Add the paired players to the pairing array
        for player in groups[group]:
            pairings.append(player)

    # Add a bye if there if there is a player still in temp after all the groups have been iterated
    if temp != None:
            pairings.append(temp)
            pairings.append("bye")

    # Call the pairing check function to check for repairings and try to correct
    pairings = pairing_check(pairings, players)

    return pairings

def pairing_check(pairings, players):
    '''
    Function to try and correct pairings when players are paired versus previous opponents.
    Takes in the players array and returns it if there are no previous opponents.
    Otherwise it swaps players to try and find a pairing without previous opponents for 5 times.
    If there is no succesfull pairings after 5 tries, return the original pairings allowing repairs
    Otherwise return the corrected pairings.
    '''
    # Initialize variables
    prev_opps = {}
    y = len(players)
    pairings_wrong = True
    z = len(pairings)
    i = 0

    # Initialize backup list in case no good pairings are found, to pair accurately on points while allowing re-pairing
    backup = pairings.copy()

    # For each player, collect a list of the opponents
    for x in range (0, y):
        prev_opps[players[x]["name"]] = [players[x]["opp1"], players[x]["opp2"], players[x]["opp3"], players[x]["opp4"]]

    # Check is the current pairings has a repair and keep trying to repair after no succes after 5 times keep the pairings as they are
    while pairings_wrong:
        pairings_wrong = False
        for x in range (0, z, 2):
            if pairings[x + 1] in prev_opps[pairings[x]]:
                pairings_wrong = True

                # Swap opponent with a player in the next match while checking for out of bounds
                if (x + 2) >= z:
                    temp = pairings [x + 1]
                    pairings[x + 1] = pairings [x - 1]
                    pairings[x - 1] = temp
                else:
                    temp = pairings [x + 1]
                    pairings[x + 1] = pairings [x + 2]
                    pairings[x + 2] = temp

        # Increment counter and check if "repair attempt" 5 has passed
        i += 1
        if i >= 5:
            return backup

    return pairings


