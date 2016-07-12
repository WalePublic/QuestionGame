# QuestionGame
Code exercise


Setup database/table and php

    Get files from gitHub
    Create a database using the commands in “DatabaseAndTables” file. (simply copy those commands to cmd line of mySQL db)
    Enter the host, username and password in the index6.php file  and save (on 66th line of index6.php . I.e "host", "username", "password"). Make sure smart quotes, smart dashes and smart copy/paste are turn off in your text editor.
    Place the index6.php file in a local address or anywhere else you can access. 
    Run QuestionGame.xcodeproj file in xCode. (after unzipping QuestionGame.zip)


Using app

    Enter address of index6.php in the address field, or you could also just change the address in the MainAddress.m file before running app (address is located on 35th line of MainAddress.m).
    Use editor in the app to insert new question and tokens. (see sample.mov video).
    You can run game by pressing Start on the Main View.
    Questions and tokens will be retrieved from database and saved in dictionary. (Limit of 20 questions at a time).
    You will be asked questions, and then tokens will be updated accordingly. 
