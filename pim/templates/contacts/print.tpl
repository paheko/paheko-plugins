<!DOCTYPE html>
<html>
<head>
    <title>Impression</title>
    <style type="text/css">
    {literal}
    @page { size: A4; margin: 1.2cm; padding: 0;}
    * { margin: 0; padding: 0; }
    body { font-family: sans-serif; }
    ul {
        list-style-type: none;
    }
    h1 {
        background: #000;
        color: #fff;
        margin: 0 .5em;
        padding: .5em;
    }
    h3 {
        float: right;
        font-weight: normal;
        clear: both;
    }
    div {
        margin: 1em;
    }
    {/literal}
    </style>
</head>
<body>

<?php $letter = null; ?>

{foreach from=$list item="contact"}
    <?php
    $l = mb_strtoupper(mb_substr(trim($contact->name . $contact->first_name), 0 , 1));

    if ($l !== $letter)
    {
        printf('<h1>%s</h1>', htmlspecialchars($l));
        $letter = $l;
    }
    ?>

    <div>

    {if $contact.title}
        <h3>{$contact.title}</h3>
    {/if}

    <h2>{$contact->getFullName()}</h2>

    <ul>

    {if $contact.mobile_phone}
        <li>{$contact.mobile_phone}</li>
    {/if}

    {if $contact.phone}
        <li>{$contact.phone}</li>
    {/if}

    {if $contact.email}
        <li>{$contact.email}</li>
    {/if}

    {if $contact.address}
        <li>{$contact.address|escape|nl2br}</li>
    {/if}

    </ul>
    </div>
{/foreach}

</body>
</html>
