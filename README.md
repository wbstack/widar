> ℹ️ Issues for this repository are tracked on [Phabricator](https://phabricator.wikimedia.org/project/board/5563/) - ([Click here to open a new one](https://phabricator.wikimedia.org/maniphest/task/edit/form/1/?tags=wikibase_cloud
))

This is the OAuth interface for many of my Wikidata tools. It relies heavily on [this OAuth code](https://bitbucket.org/magnusmanske/magnustools/src/2f3811e80d0b215f9b45fb9b6da2d57536c56077/public_html/php/oauth.php?at=master&fileviewer=file-view-default).

## Syncing this fork
- Switch/Create a branch for the merge
- Add local upstream remote: `git remote add upstream https://bitbucket.org/magnusmanske/widar/src/master/`
- Fetch upstream: `git fetch upstream`  
- Merge master(!) branch: `git merge upstream/master`
- Resolve conflicts (if any)
- Update Changelog

