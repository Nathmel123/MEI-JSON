# This project is part of the Liszt-Portal at [SLUB Dresden](https://slub-dresden.de)
This will be integrated into the [liszt_common module](https://github.com/dikastes/liszt_common)

# Purpose

This php script converts MEI-xml to JSON. If necessary, the xml-id can be included in the output file. Also configurable, this script adds a literal string to the output file, to store the order of mixed content elements. Furthermore the outputfile can be split into multiple files, defining split symbols in the config file. A dtd file for the config file is also provided, id addition to a working programm, the config file must be valid. 