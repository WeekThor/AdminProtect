# AdminProtect
Plugin prevents banning and kicking of players with special rights

## Commands
- /kick <player> [reason...] – kick player
- /ban <player> [reason...] – ban player
- /banip <player or IP adress> [reason...] – ban specified IP adress or specified player's IP adress
- /unban <player>; /pardon <player> – unban player
- /unbanip <IP>; /pardon-ip <IP> – unban IP adress
  
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
  
