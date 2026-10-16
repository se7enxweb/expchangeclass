<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE TS>
<!-- Hebrew (עברית, heb-IL) translation of the Exponential administration interface, written right to left.
     Machine-assisted; a review by a native speaker is welcome. -->
<TS version="2.0" language="heb-IL">
<context>
    <name>design/admin/changeclass/action</name>
    <message>
        <source>Convert Instructions for converting class &apos;%source_class_name&apos; to &apos;%dest_class_name&apos;</source>
        <translation>הוראות המרה להמרת המחלקה &apos;%source_class_name&apos; ל־&apos;%dest_class_name&apos;</translation>
    </message>
    <message>
        <source>&lt;b&gt;The object was NOT converted.&lt;/b&gt; Nothing was changed; the content is exactly as it was.</source>
        <translation>&lt;b&gt;האובייקט לא הומר.&lt;/b&gt; דבר לא שונה; התוכן נשאר בדיוק כפי שהיה.</translation>
    </message>
    <message>
        <source>Reason:</source>
        <translation>סיבה:</translation>
    </message>
    <message>
        <source>The object was converted to &apos;%dest_class_name&apos;.</source>
        <translation>האובייקט הומר ל־&apos;%dest_class_name&apos;.</translation>
    </message>
    <message>
        <source>A file with all params for this convertion has been created in &apos;%convert_file_path&apos; and can be used for batch convertions of all remaining (%source_object_count objects) &apos;%source_class_name&apos; to class &apos;%dest_class_name&apos;.</source>
        <translation>נוצר קובץ עם כל הפרמטרים להמרה זו ב־&apos;%convert_file_path&apos;, וניתן להשתמש בו להמרות באצווה של כל האובייקטים הנותרים של &apos;%source_class_name&apos; (%source_object_count אובייקטים) למחלקה &apos;%dest_class_name&apos;.</translation>
    </message>
    <message>
        <source>&lt;b&gt;ERROR:&lt;/b&gt; eZFile could not store parameter file!</source>
        <translation>&lt;b&gt;שגיאה:&lt;/b&gt; eZFile לא הצליח לשמור את קובץ הפרמטרים!</translation>
    </message>
    <message>
        <source>Do you have write access to path (%convert_file_path)?</source>
        <translation>האם יש לך הרשאת כתיבה לנתיב (%convert_file_path)?</translation>
    </message>
    <message>
        <source>Command line command for running this script would be like this:</source>
        <translation>הפקודה בשורת הפקודה להרצת סקריפט זה תיראה כך:</translation>
    </message>
    <message>
        <source>Notice:</source>
        <translation>הודעה:</translation>
    </message>
    <message>
        <source>* Switch &apos;example&apos; with the name of your siteaccess, preferably the one you are using right now!&lt;br /&gt;&amp;nbsp; ( so your sure it uses same db and cache folder )</source>
        <translation>* יש להחליף את &apos;example&apos; בשם גישת האתר שלך, רצוי זו שבה נעשה שימוש כרגע!&lt;br /&gt;&amp;nbsp; ( כדי לוודא שנעשה שימוש באותו מסד נתונים ובאותה תיקיית מטמון )</translation>
    </message>
    <message>
        <source>Content of convert &apos;paramfile&apos; (%convert_file_name):</source>
        <translation>תוכן קובץ הפרמטרים &apos;paramfile&apos; של ההמרה (%convert_file_name):</translation>
    </message>
    <message>
        <source>Continue to new object</source>
        <translation>המשך לאובייקט החדש</translation>
    </message>
</context>
<context>
    <name>design/admin/changeclass/map_attributes</name>
    <message>
        <source>Change object content class &amp;lt;%name&amp;gt; [%class_name]</source>
        <translation>שינוי מחלקת התוכן של האובייקט &amp;lt;%name&amp;gt; [%class_name]</translation>
    </message>
    <message>
        <source>Warnings:</source>
        <translation>אזהרות:</translation>
    </message>
    <message>
        <source>Destination class has at least one unsupported datatype. You can&apos;t use this class.</source>
        <translation>למחלקת היעד יש לפחות סוג נתונים אחד שאינו נתמך. לא ניתן להשתמש במחלקה זו.</translation>
    </message>
    <message>
        <source>Destination class is not configured to be container!</source>
        <translation>מחלקת היעד אינה מוגדרת כמכל!</translation>
    </message>
    <message>
        <source>The following attributes can not be mapped:</source>
        <translation>לא ניתן למפות את המאפיינים הבאים:</translation>
    </message>
    <message>
        <source>No warnigs for this operation</source>
        <translation>אין אזהרות לפעולה זו</translation>
    </message>
    <message>
        <source>Back to destination class selection</source>
        <translation>חזרה לבחירת מחלקת היעד</translation>
    </message>
    <message>
        <source>Attributes mapping</source>
        <translation>מיפוי מאפיינים</translation>
    </message>
    <message>
        <source>Source class (%source_class)</source>
        <translation>מחלקת מקור (%source_class)</translation>
    </message>
    <message>
        <source>Destination class (%destination_class)</source>
        <translation>מחלקת יעד (%destination_class)</translation>
    </message>
    <message>
        <source>(leave empty)</source>
        <translation>(להשאיר ריק)</translation>
    </message>
    <message>
        <source>Options:</source>
        <translation>אפשרויות:</translation>
    </message>
    <message>
        <source>Keep original object creator and creation date</source>
        <translation>שמירת היוצר ותאריך היצירה המקוריים של האובייקט</translation>
    </message>
    <message>
        <source>Add source object&apos;s children to new object</source>
        <translation>הוספת פריטי המשנה של אובייקט המקור לאובייקט החדש</translation>
    </message>
    <message>
        <source>Generate parameters for converting all instancees of this class</source>
        <translation>יצירת פרמטרים להמרת כל המופעים של מחלקה זו</translation>
    </message>
    <message>
        <source>Modify Object</source>
        <translation>שינוי האובייקט</translation>
    </message>
</context>
<context>
    <name>design/admin/changeclass/select_class</name>
    <message>
        <source>Change object content class &amp;lt;%name&amp;gt; [%class_name]</source>
        <translation>שינוי מחלקת התוכן של האובייקט &amp;lt;%name&amp;gt; [%class_name]</translation>
    </message>
    <message>
        <source>General object&apos;s informations</source>
        <translation>מידע כללי על האובייקט</translation>
    </message>
    <message>
        <source>Content class name:</source>
        <translation>שם מחלקת התוכן:</translation>
    </message>
    <message>
        <source>Children count:</source>
        <translation>מספר פריטי משנה:</translation>
    </message>
    <message>
        <source>Languages:</source>
        <translation>שפות:</translation>
    </message>
    <message>
        <source>Current version:</source>
        <translation>גרסה נוכחית:</translation>
    </message>
    <message>
        <source>Related objects [%count]:</source>
        <translation>אובייקטים קשורים [%count]:</translation>
    </message>
    <message>
        <source>no objects</source>
        <translation>אין אובייקטים</translation>
    </message>
    <message>
        <source>Reverse related objects [%count]:</source>
        <translation>אובייקטים המקושרים לפריט זה [%count]:</translation>
    </message>
    <message>
        <source>Attributes (datatype):</source>
        <translation>מאפיינים (סוג נתונים):</translation>
    </message>
    <message>
        <source>Destination content class:</source>
        <translation>מחלקת תוכן יעד:</translation>
    </message>
    <message>
        <source>%identifier (unnamed)</source>
        <translation>%identifier (ללא שם)</translation>
    </message>
    <message>
        <source>Select</source>
        <translation>בחירה</translation>
    </message>
</context>
<context>
    <name>design/admin/changeclass</name>
    <message>
        <source>Change content class</source>
        <translation>שינוי מחלקת תוכן</translation>
    </message>
</context>
</TS>
