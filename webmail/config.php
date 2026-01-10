
/**
 * File types deny list (Regexp)
 * File types listed in this regexp won't be saved locally,
 * the user will see the files but not be able to download them
 *
 * If set to NULL then only types listed in the whitelist can be saved
 * If the whitelist is NULL, then everything will be allowed, except types in this list
 */
const FILE_TYPES_DENY = '!^(?:
	application/(?:octet-stream|x-msdownload|x-msi|x-msdos-program|vnd\.microsoft\.portable-executable)
	|application/x-apple-diskimage
	|application/x-sh
	|text/x-shellscript
	)$!x';

/**
 * File types allow list (regexp)
 * Types listed in this regexp will be saved locally and can be downloaded by the user
 */
//const FILE_TYPES_ALLOW = '!^(?:audio|video|image)/|^application/(?:ogg|pdf|msword|vnd\.oasis.*)$!';
const FILE_TYPES_ALLOW = null;

/**
 * File types that can be displayed in a frame directly in the browser
 * The browser should be able to display those type
 */
const FILE_TYPES_INLINE = [
	'application/pdf',
	'audio/mpeg',
	'audio/ogg',
	'audio/wave',
	'audio/wav',
	'audio/x-wav',
	'audio/x-pn-wav',
	'audio/webm',
	'video/webm',
	'video/ogg',
	'application/ogg',
	'video/mp4',
	'text/plain',
	'image/png',
	'image/gif',
	'image/webp',
	'image/svg+xml',
];