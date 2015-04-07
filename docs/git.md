# Pubcie branch strategy

We keep the following branches:

- **master**: follows what is live or might go live any second
- dev: follows the main line of development; you don't want to break this one
- **issue-n**/**feature-n**: ticket specific branches, possibly awaiting merge to dev

So your workflow should be, depending on the code you're submitting:

- **critical fix, only when approved by team**: single commit, push to master
- **tiny bugfix**: push to dev; make sure you test properly!
- *otherwise*: push to feature branch and post notification in `#pull-request` on slack when done

Make sure you delete a branch on the repo when it is merged into the mainline.

## External developers

We might have some external devs that don't have write-access to the repo.
They will have to fork the `csrdelft.nl` dev branch and make there commits in their own repo.
Then they can post a PR in `#pull-request`.
