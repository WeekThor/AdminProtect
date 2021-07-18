# AdminProtect
AdminProtect is PocketMine-MP plugin that protects the administrator (or players with permissions) from being banned and kicked and prevent offline player ban.

## Commands
- /kick &lt;player&gt; [reason...] – kick player
- /ban &lt;player&gt; [reason...] – ban player
- /tempban &lt;player&gt; &lt;date or duration&gt; [reason...] – ban player temporary
- /banip &lt;player or IP adress&gt; [reason...] – ban specified IP adress or specified player's IP adress
- /unban &lt;player&gt; /pardon &lt;player&gt; – unban player
- /unbanip &lt;IP&gt; /pardon-ip &lt;IP&gt; – unban IP adress
  
## Permissions
- admin.protect.* – all plugin permissions
  - admin.protect.kick – Protection from /kick
  - admin.protect.kick.use – Allow to use /kick
  - admin.protect.kick.use.protected – Allow to kick players with protection
  - admin.protect.ban – Protection from /ban
  - admin.protect.ban.use – Allow to use /ban
  - admin.protect.ban.use.offline – Allow to ban offline players
  - admin.protect.ban.use.protected – Allow to ban players with protection
  - admin.protect.tempban – Protection from /tempban
  - admin.protect.tempban.use – Allow to use /tempban
  - admin.protect.tempban.use.offline – Allow to temporary ban offline players
  - admin.protect.tempban.use.protected – Allow to temporary ban players with protection
  - admin.protect.unban.use – Allow to use /unban
  - admin.protect.banip – Protection from /banip
  - admin.protect.banip.use – Allow to use /banip (only ban online players' IP)
  - admin.protect.banip.use.offline – Allow to ban offline players' IP
  - admin.protect.banip.use.protected – Allow to IP-ban players with protection
  - admin.protect.unbanip.use – Allow to /unbanip
  
## Temporary ban
Command: /tempban &lt;player&gt; &lt;date or duration&gt; [reason...]

Aliases: /tban

Arguments: 
- &lt;player&gt; – player nickname
- &lt;date or duration&gt; – ban end date (01.01.2026) or ban duration (1h30m, 5mo, etc)
- [reason...] – ban reason

Ban duration examples:
- 1h – 1 hour
- 1m – 1 minute
- 1d – 1 day
- 1w – 1 week
- 1mo – 1 month
- 1y – 1 year

You can combine: 1h30m – 1 hour 30 minutes; 1w3d – 1 week 3 days, etc
