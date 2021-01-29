# AdminProtect
AdminProtect is PocketMine-MP plugin that protects the administrator (or players with permissions) from being banned and kicked and prevent offline player ban.

## Commands
- /kick &lt;player&gt; [reason...] – kick player
- /ban &lt;player&gt; [reason...] – ban player
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
  - admin.protect.unban.use – Allow to use /unban
  - admin.protect.banip – Protection from /banip
  - admin.protect.banip.use – Allow to use /banip (only ban online players' IP)
  - admin.protect.banip.use.offline – Allow to ban offline players' IP
  - admin.protect.banip.use.protection – Allow to IP-ban players with protection
  - admin.protect.unbanip.use – Allow to /unbanip
  
