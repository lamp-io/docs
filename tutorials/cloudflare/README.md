# Cloudflare

## how to configure a lamp.io application as an origin in Cloudflare

### lamp.io side
- create a lamp.io app or chose an existing one

### Cloudflare side
- login to your Cloudflare account at https://dash.cloudflare.com
- add your site by clicking the "+Add a Site" button and following the instructions
-- note that you will need the Enterprise plan
- navigate to your site and click "Page Rules" in the top navigation menu
- click the "Create Page Rule" button
- put `yoursite.com/*` in the url field to match all urls
- click the "+Add a Setting" link
- chose "Host Header Override" from the pulldown
- put your app in the field next to it, for example `app-xxxxx.lamp.app`
- click "Save and Deploy"
