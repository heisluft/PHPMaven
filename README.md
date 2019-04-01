# PHPMaven 0.3.0
A simple PHP implementation of a maven repo.

### Config
PHPMaven is configured via the repo.json file in which you can currently configure
authentication, both if it is needed and key-value pairs for each valid login
credentials. Currently, PHPMaven only supports BasicAuthentication.
DigestAuthentication may be supported in a future release.

### Uploading artifacts
Artifacts are uploaded via HTTP PUT request onto the URL under which the uploaded
file is to be found. PHPMaven WILL check the MD5 and SHA1 Checksums and block the
upload with a "Failed Dependency" Header while deleting the original file, if one
of the sums doesn't match. PHPMaven won't, however, check the GPG Signature.

### Indexing
PHPMaven functions as a file viewer. Currently, this cannot be changed. Future
versions may implement this behaviour.

### Changelog
* 0.2.0: merged upload and indexing functionality
* 0.3.0: implemented Basic Authentication, configurable in the new repo.json file.