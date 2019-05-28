---
id: version-3.0-troubleshooting
title: Troubleshooting
sidebar_label: Troubleshooting
original_id: troubleshooting
---

**Error: Maximum function nesting level of '100' reached**

Webonyx's GraphQL library tends to use a very deep stack. 
This error does not necessarily mean your code is going into an infinite loop.
Simply try to increase the maximum allowed nesting level in your XDebug conf:

```
xdebug.max_nesting_level=500
```
