# Language-Identification
The source codes of 5 statistical algorithms (i.e. CBA, WBA, SCA, HA1 and HA2) that were conceived for language identification. Hence, the algorithms are featured only with 32 languages belonging to DLI32 corpus, and they figured out quite interesting accuracies comparing to Google LID API and Microsoft Office LID (refer to publications for more details).

#CBA
The CBA algorithm is based on identifying the language using the characters of each language, where it consists of computing the sum of the character frequencies of each language, and consequently classifying the promising language corresponding to the one having the highest sum.

#WBA
The WBA algorithm is based on identifying the language using the common words of each language, where it consists of computing the sum of the word frequencies of each language, and consequently classifying the promising language corresponding to the one having the highest sum.

#SCA
The SCA algorithm is based on identifying the language using the special characters of each language, and it is similar to CBA. However, the classification is performed on two different stages.

#HA1
The HA1 algorithm is a sequential combination between two algorithms (CBA and WBA), where the first one is based on the language characters, and the second one is based on the language common words. The HA1 algorithm consists of executing firstly the CBA algorithm, and if the promising language is Chinese, Hebrew, Greek, Thai or Hindi, we then return this language. Otherwise, the WBA algorithm is executed secondly

#HA2
The HA2 algorithm is a combination between two algorithms (CBA and WBA), where the first one is based on the language characters, and the second one is based on the language common words. The HA2 algorithm consists of adding the sum of frequencies of the two algorithms for each language, andconsequently, the promising language is the one having the highest new sum.
