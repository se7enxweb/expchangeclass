<?php /* #?ini charset="utf-8"?

[General]
#Logs changes done using the batch script in your cache folder
#in the file: logExpChangeClass.txt
ScriptLog=enabled

#List of unsupported datatypes
UnsupportedDataTypeArray[]
UnsupportedDataTypeArray[]=ezuser


# Custom mapping of datatypes
# makes it possible to map one datatype to another
# thru your own scripts
# example:
# [ezstring]
# SupportedDestination[]
# SupportedDestination[]=eztext
# SupportedDestination[]=ezxmltext
# Script=extension/expchangeclass/classes/converters.php
# Class=myConverterClass
# Function=convert
#
# You'll get the $newAttribute byRef, $sourceAttribute byVal and $destinationAttribute byVal
#
# A group is keyed on the SOURCE datatype and carries exactly one script, so a
# source with several destinations needs one entry point that dispatches on the
# destination datatype. Declaring the group twice does not work - the second
# occurrence wins and the destinations listed in the first become unreachable,
# which is how ezstring to ezxmltext came to be silently unavailable.

[ezstring]
SupportedDestination[]
SupportedDestination[]=ezxmltext
SupportedDestination[]=ezinteger
SupportedDestination[]=ezfloat
Script=extension/expchangeclass/classes/converters.php
Class=expChangeClassConverters
Function=convertString

[eztext]
SupportedDestination[]
SupportedDestination[]=ezxmltext
SupportedDestination[]=ezinteger
SupportedDestination[]=ezfloat
Script=extension/expchangeclass/classes/converters.php
Class=expChangeClassConverters
Function=convertString

[ezobjectrelationlist]
SupportedDestination[]
SupportedDestination[]=ezobjectrelationlistbloc
Script=extension/expchangeclass/classes/converters.php
Class=expChangeClassConverters
Function=convertRelationListToBloc

[SimpleConversion]
# Simple conversion between different datatypes
# example:
# SupportedConversion[]=source_datatype;destination_datatype
SupportedConversion[]
SupportedConversion[]=eztext;ezstring
SupportedConversion[]=ezstring;eztext
SupportedConversion[]=ezemail;eztext
SupportedConversion[]=ezemail;ezstring

*/?>
