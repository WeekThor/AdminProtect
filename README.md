# AdminProtect
AdminProtect is PocketMine-MP plugin that protects the administrator (players with special permissions) from being banned and kicked and prevent offline player ban.

Now supports PMMP api 4.0.0+ only.

## Commands
- /kick &lt;player&gt; [reason...] – kick specified player
- /ban &lt;player&gt; [reason...] – ban specified player
- /tempban &lt;player&gt; &lt;date or duration&gt; [reason...] – temporary ban specified player
- /banip &lt;player or IP adress&gt; [reason...] – ban specified IP adress or specified player's IP adress
- /tbanip &lt;player IP adress&gt; &lt;date or duration&gt; [reason...] – temporary ban specified IP adress or specified player's IP adress
- /unban &lt;player&gt; /pardon &lt;player&gt; – unban specified player
- /unbanip &lt;IP&gt; /pardon-ip &lt;IP&gt; – unban specified IP adress
  
## Permissions
- adminprotect.* – all plugin permissions
  - adminprotect.kick.protect – Protection from /kick
  - adminprotect.kick.use – Allow to use /kick
  - adminprotect.kick.use.protected – Allow to kick players with protection
  - adminprotect.ban.protect – Protection from /ban
  - adminprotect.ban.use – Allow to use /ban
  - adminprotect.ban.use.offline – Allow to ban offline players
  - adminprotect.ban.use.protected – Allow to ban players with protection
  - adminprotect.tempban.protect – Protection from /tempban
  - adminprotect.tempban.use – Allow to use /tempban
  - adminprotect.tempban.use.offline – Allow to temporary ban offline players
  - adminprotect.tempban.use.protected – Allow to temporary ban players with protection
  - adminprotect.unban.use – Allow to use /unban
  - adminprotect.banip.protect – Protection from /banip
  - adminprotect.banip.use – Allow to use /tbanip (only ban online players' IP and only temporary ban)
  - adminprotect.banip.use.offline – Allow to ban offline players' IP
  - adminprotect.banip.use.protected – Allow to IP-ban players with protection
  - adminprotect.banip.use.permanent – Allow to use /banip (for permanentrly ban)
  - adminprotect.unbanip.use – Allow to /unbanip

## Features
- Protect admins from beign kicked, banned or ip-baned
- Protect offline players from beign baned or ip-baned (we can't check if offline players have protection permissions)
- - Players without ```adminprotect.banip.use.offline``` can't ban specified ip-adress, they can only ban specified online player nick
- Special permissions for operators to ban and kick players with protection
- Temporary ban and ip-ban
- Broadcasting kick and ban messages for all players
- Simple ban duration setting 
  
## Temporary ban
You can specify the ban until date in the format dd.mm.YYYY (for example: 03.12.2022) or you can specify the ban duration time in the special format decribed below.

(Ban duration is specifying without spaces)

Ban duration format:
- 1s - 1 second
- 1m – 1 minute
- 1h – 1 hour
- 1d – 1 day
- 1w – 1 week
- 1mo – 1 month
- 1y – 1 year

You can combine: ```1h30m``` – 1 hour 30 minutes; ```1w3d``` – 1 week 3 days, etc
