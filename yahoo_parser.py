import pandas as pd
import re
import sys

from urllib.request import urlopen
from lxml import etree
from numpy import *

# TODO: Find a way to authenticate with Yahoo so I can read these directly.
teams_url = 'https://football.fantasysports.yahoo.com/archive/nfl/2017/35095/teams'
draft_results_url = 'https://football.fantasysports.yahoo.com/archive/nfl/2017/35095/draftresults'

teams = {}
team_keys = ['Offense', 'Kicker', 'Defense']
headers = {'Offense': ['Fan Pts', 'Passing Inc', 'PassYds', 'PassTD', 'Int', 'RushAtt', 'RushYds', 'RushTD', 'Tgt', 'Rec', 'RecYds', 'RecTDs', 'RetYDs', 'RetTDs', '2Pt', 'Fum', 'Lost', 'Draft', 'WBD', 'Round', 'Keeper Cost'], 'Defense': ['Fan Pts', 'RetYds', 'RetTDs', 'TackSolo', 'TackAsst', 'TFL', 'Sack', 'Safety', 'PassDef', 'BlkKick', 'Int', 'FumForc', 'FumRec', 'TD', 'Draft', 'WBD', 'Round', 'Keeper Cost']}

# Iterate over each team and parse players into DataFrames by team designation.
for i in range(14):
    team = {}

    team_page = 'team_pages/team' + str(i) + '.htm'
    print("Opening " + team_page)
    f = open(team_page, 'r')
    team_name = ''
    found = False
    for line in f:
        # TODO: Find a better way to do this.
        if not found and line.find('<div id="team-nav"') != -1:
            found = True
        elif found and line.find('<em>') != -1:
            team_name = line.split('<em>')[1].split('</em>')[0]
            break;
    if team_name == '':
        sys.exit('Error: could not parse team name out of ' + team_page)
    #team_name = team_name.replace('[^A-Za-z 0-9\#\'\!\&]+', '').strip()
    print("Found team name: " + team_name)

    df_list = pd.read_html(team_page, index_col=0, match='Player')
    for i, df in enumerate(df_list):
        df.info()
        df.index = df.index.str.replace('[^A-Za-z\. ]+', '').str.strip()
        #print("Index: " + str(df.index))
        #print("Columns: " + str(df.columns))
        #print(df)
        df.pop(df.columns[0]) # Drop 'Bye'
        if (i != 1): # Just skip kickers
          team[team_keys[i]] = df
    teams[team_name] = team

draft_result_list = []

# Iterate over draft results for all teams and concatenate into one table
dr_list = pd.read_html('Draft Results _ Fantasy Football _ Yahoo! Sports.htm', index_col=2)
for i, df in enumerate(dr_list):
    if (i > 0):
      df.index = df.index.str.replace(r'\(.+\)', '')
      df.index = df.index.str.replace('[^A-Za-z\. ]+', '').str.strip()
      df['order'] = df['Unnamed: 1'].str.replace('[\(\)]','').astype('uint64')
      #df.info()

      team_name = df.columns[0]
      df.pop(team_name)
      df.pop('Unnamed: 1')

      #print(df)
      draft_result_list.append(df)

draft_results = pd.concat(draft_result_list)

# Join draft results back into team offense and defense tables.
for key, team in teams.items():
      team['Offense'] = pd.merge(team['Offense'], draft_results, how='left', left_index=True, right_index=True, validate='one_to_one')
      team['Defense'] = pd.merge(team['Defense'], draft_results, how='left', left_index=True, right_index=True, validate='one_to_one')

players = []

# Concatenate all tables' scoring data to produce would-be-drafted based on overall point ranking.
for key, team in teams.items():
   players.append(team['Offense'].iloc[:, [0]])
   players.append(team['Defense'].iloc[:, [0]])

player_list = pd.concat(players)
#print(str(player_list))
player_list = player_list.reindex(columns=[player_list.columns[0]])
wbd = player_list.rank(method='min', ascending=False)
wbd.columns = (['wbd'])
wbd['wbd'] = wbd['wbd'].astype('float64').div(14.0).add(1.0).astype('uint64')
wbd = wbd.sort_values(by='wbd')
print(str(wbd))

def convert_undrafted_to_round_22(x):
    if isnan(x):
        return 22.0
    else:
        return x

# Join result back into back into each team's offense and defense tables and calculate keeper cost and write to Excel
writer = pd.ExcelWriter('Smash and Dash Keeper Sheet 2018.xlsx')
for key, team in teams.items():
    team_name = key
    print("Team for " + team_name)
    for key, side in team.items():
      side = pd.merge(side, wbd, how='left', left_index=True, right_index=True, validate='one_to_one')
      side['round'] = side['order'].astype('float64').div(14.0).add(1.0).apply(convert_undrafted_to_round_22).astype('uint64')
      side['keeper_cost'] = side[['wbd', 'round']].reindex(columns=['wbd', 'round']).astype('float64').mean(axis=1).astype('uint64')
      print(key + ': ' + str(side))
      side.to_excel(writer, team_name + " - " + key, header=headers[key])

writer.save()
