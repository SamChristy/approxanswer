Approximate Answer Type
=======================

### Explanation ###
This is a question type plugin for Moodle, which was created to improve upon the Short Answer 
question type. Moodle is a popular open source Learning Management System; for more information, 
please visit the [project's website](https://moodle.org/).

This question type is similar to the Short Answer question type, except that the user's answer is 
matched against the correct answer, or answers, *phonetically* (with a small margin for error, 
proportional to the word’s length) as opposed to *exactly*. This question type also displays the 
matching answer in the feedback for questions that were answered correctly, as this makes more 
sense.

#### Installation & Use ####
The installation process is identical to that of any other question type plugin. Once you have 
downloaded the plugin, simply drop it into Moodle’s ‘/question/type’ directory, then visit 
‘yoursite/admin/’ and follow the instructions.

You may provide as many correct answers as you wish. The first matching answer will be used to 
determine the user's score and feedback. Please note that the algorithms used by this module are 
English-centric and the answers are automatically transliterated to the Latin character set.

### Versions ###
This plugin has been tested for compatibility with Moodle versions 2.2 and 2.4, 2.3 should also
work but has not been specifically tested.

#### Coming Soon ####
The following features are planned for future releases:
- XML export and import options
- Unit test suite
- Optional mark scaling based on accuracy of spelling

##### License #####
The code is licensed under the [GNU GPL v3.0](http://www.gnu.org/licenses/gpl-3.0.html).

©Copyright Sam Christy 2012