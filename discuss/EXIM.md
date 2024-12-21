Copy the `exim_webhook_example.php` file to a directory, and set `chmod +x` on it.

Now add the router to your Exim configuration:

```
webhook_router:
  driver = accept
  local_part_suffix = +*
  local_part_suffix_optional
  data = ${lookup{$local_part@$domain}wildlsearch{/home/mail/webhooks.aliases}}
  transport = webhook_transport
```

Now add the transport:

```
webhook_transport:
  driver = pipe
  command = /home/mail/webhook.php "${local_part}@${domain}" "${lookup{$local_part@$domain}wildlsearch{/home/mail/webhooks.aliases}}"
  return_fail_output
  log_fail_output
  log_defer_output
  temp_errors = 75
  timeout = 60s
```

Create a new file in `/home/mail/webhooks.aliases` like this:

```
*@lists.example.org: https://test:secret@example.org/p/discuss/receive.php?to=%s
contact@example.org: https://test:secret@example.org/p/discuss/receive.php?to=%s
```