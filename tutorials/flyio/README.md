# Fly.io

## how to configure a lamp.io application as an origin in fly.io

### lamp.io side
- create a lamp.io app or chose an existing one

### fly.io side
#### create an app
- login to your account at https://fly.io
- click the "Add new app" button
- click the "Create App" button

#### configure its origin
- in the left side menu click "CDN"
- scroll down to "Backends" and click the "Edit" pencil icon
- in the "URL" field put the url for your lamp.io app. for example: `https://app-xxxxx.lamp.app`
- in the "Value" field for the Host header put the same thing but without `https://`. for example: `app-xxxxx.lamp.app`
- click "Save"
- in the left side menu click "Releases" to verify the config release completed
- in the left side menu click "Overview"
- click the external link at the top, it will look like `word-word-123`
- you should see the output of your lamp.io app

#### configure your hostname
- back in the fly.io interface, in the left side menu, click "Hostnames"
- click the "Add hostname" button
- type in your hostname and click the "Add hostname" button
- click the "View" button that appears and follow the instructions
