aecho "# gasavbot" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin https://github.com/damianperez/gasavbot.git
git push -u origin main

store cred

 git config --global credential.helper store
