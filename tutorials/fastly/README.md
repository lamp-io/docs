# Fastly

## how to configure a lamp.io application as an origin in Fastly

- create a lamp.io app or chose an existing one
- login to your fastly account at https://manage.fastly.com
- click the "+CREATE SERVICE" button near the top right
- click the "CREATE YOUR FIRST DOMAIN" button
- put in the domain name you wish to support
- click the "CREATE" button
- click "Origins" on the left side menu
- click the "+CREATE YOUR FIRST HOST" button
- name it `lamp.io`
- put the host for your app in as the Address, for example: `app-abc12.lamp.app`
- put the same string/value in the "Certificate hostname" field
- check the box below "SNI hostname" labeled "Match the SNI hostname to the Certificate hostname."
- scroll to the bottom and click the "CREATE" button
- click "Settings" on the left side menu
- toggle the "Override host" button to ON
- put the same string as in the "Address" and "Certificate hostname" fields above
- click the "SAVE" button
- in the top right corner click the green "ACTIVATE" button
