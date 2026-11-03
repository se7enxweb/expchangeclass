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
<context>
    <name>extension/expchangeclass</name>
    <message>
        <source>Change many objects</source>
        <translation>שינוי אובייקטים רבים</translation>
    </message>
    <message>
        <source>Every published object of a class below a node, changed with one mapping in the background. You can leave the progress page; each object gets its own check, restore point and transaction, and one that cannot be changed is skipped with the reason.</source>
        <translation>כל אובייקט שפורסם של מחלקה מתחת לצומת, משתנה ברקע לפי מיפוי אחד. אפשר לעזוב את דף ההתקדמות; כל אובייקט מקבל בדיקה, נקודת שחזור וטרנזקציה משלו, ואובייקט שלא ניתן לשנות מדולג עם ציון הסיבה.</translation>
    </message>
    <message>
        <source>Background jobs need Exponential 6.0.15 or later. Use the command shown below.</source>
        <translation>משימות רקע דורשות Exponential 6.0.15 ואילך. השתמשו בפקודה המוצגת למטה.</translation>
    </message>
    <message>
        <source>Classes and subtree</source>
        <translation>מחלקות ותת־עץ</translation>
    </message>
    <message>
        <source>Objects of the class</source>
        <translation>אובייקטים של המחלקה</translation>
    </message>
    <message>
        <source>Change into</source>
        <translation>שינוי למחלקה</translation>
    </message>
    <message>
        <source>Below the node (ID)</source>
        <translation>מתחת לצומת (מזהה)</translation>
    </message>
    <message>
        <source>Show the preview</source>
        <translation>הצגת תצוגה מקדימה</translation>
    </message>
    <message>
        <source>%count objects of %class below %node</source>
        <translation>אובייקטים של %class מתחת ל־%node: %count</translation>
    </message>
    <message>
        <source>preview this one</source>
        <translation>תצוגה מקדימה של זה</translation>
    </message>
    <message>
        <source>and %count more</source>
        <translation>ועוד %count</translation>
    </message>
    <message>
        <source>The same from a shell</source>
        <translation>אותו הדבר משורת הפקודה</translation>
    </message>
    <message>
        <source>Changed a select? Update the preview first: the job applies exactly what the preview shows.</source>
        <translation>שיניתם בחירה? עדכנו קודם את התצוגה המקדימה: המשימה מחילה בדיוק את מה שהתצוגה המקדימה מציגה.</translation>
    </message>
    <message>
        <source>Update the preview</source>
        <translation>עדכון התצוגה המקדימה</translation>
    </message>
    <message>
        <source>Start the background job</source>
        <translation>הפעלת משימת הרקע</translation>
    </message>
    <message>
        <source>Change class</source>
        <translation>שינוי מחלקה</translation>
    </message>
    <message>
        <source>Change the class of content that exists: an article that should be a blog post, a folder that should be a landing page. Open the context menu of an item in the content tree or the sub-items list and choose &quot;Change content class&quot;. You see what happens to each attribute before anything is written, and every change keeps a restore point to undo it.</source>
        <translation>שינוי המחלקה של תוכן קיים: מאמר שצריך להיות פוסט בבלוג, תיקייה שצריכה להיות דף נחיתה. פתחו את תפריט ההקשר של פריט בעץ התוכן או ברשימת פריטי המשנה ובחרו &quot;שינוי מחלקת תוכן&quot;. לפני שדבר נכתב תראו מה קורה לכל מאפיין, וכל שינוי שומר נקודת שחזור לביטולו.</translation>
    </message>
    <message>
        <source>restorable changes</source>
        <translation>שינויים הניתנים לשחזור</translation>
    </message>
    <message>
        <source>undone</source>
        <translation>בוטלו</translation>
    </message>
    <message>
        <source>restore points discarded</source>
        <translation>נקודות שחזור שנזנחו</translation>
    </message>
    <message>
        <source>rolled back</source>
        <translation>הוחזרו לאחור</translation>
    </message>
    <message>
        <source>Recent class changes</source>
        <translation>שינויי מחלקה אחרונים</translation>
    </message>
    <message>
        <source>Your recent class changes</source>
        <translation>שינויי המחלקה האחרונים שלך</translation>
    </message>
    <message>
        <source>Filter</source>
        <translation>סינון</translation>
    </message>
    <message>
        <source>State</source>
        <translation>מצב</translation>
    </message>
    <message>
        <source>Every state</source>
        <translation>כל המצבים</translation>
    </message>
    <message>
        <source>Changed, restorable</source>
        <translation>שונה, ניתן לשחזור</translation>
    </message>
    <message>
        <source>Undone</source>
        <translation>בוטל</translation>
    </message>
    <message>
        <source>Restore point discarded</source>
        <translation>נקודת השחזור נזנחה</translation>
    </message>
    <message>
        <source>Rolled back</source>
        <translation>הוחזר לאחור</translation>
    </message>
    <message>
        <source>When</source>
        <translation>מתי</translation>
    </message>
    <message>
        <source>Object</source>
        <translation>אובייקט</translation>
    </message>
    <message>
        <source>Change</source>
        <translation>שינוי</translation>
    </message>
    <message>
        <source>By</source>
        <translation>על ידי</translation>
    </message>
    <message>
        <source>Actions</source>
        <translation>פעולות</translation>
    </message>
    <message>
        <source>Restore…</source>
        <translation>שחזור…</translation>
    </message>
    <message>
        <source>%name becomes %class again, with its attributes as they were.</source>
        <translation>%name יחזור להיות מהמחלקה %class, עם המאפיינים כפי שהיו.</translation>
    </message>
    <message>
        <source>Restore</source>
        <translation>שחזור</translation>
    </message>
    <message>
        <source>No class changes yet.</source>
        <translation>אין עדיין שינויי מחלקה.</translation>
    </message>
    <message>
        <source>Restore points are kept in %directory until they are discarded or purged (./console exp:changeclass --purge=&lt;days&gt; --apply).</source>
        <translation>נקודות שחזור נשמרות ב־%directory עד שהן נזנחות או מנוקות (./console exp:changeclass --purge=&lt;days&gt; --apply).</translation>
    </message>
    <message>
        <source>Every object of a class in a part of the tree, with one mapping, as a background job with a progress page. Each object gets its own preview check, restore point and transaction; an object that cannot be changed is skipped with the reason.</source>
        <translation>כל אובייקט של מחלקה בחלק מהעץ, לפי מיפוי אחד, כמשימת רקע עם דף התקדמות. כל אובייקט מקבל בדיקת תצוגה מקדימה, נקודת שחזור וטרנזקציה משלו; אובייקט שלא ניתן לשנות מדולג עם ציון הסיבה.</translation>
    </message>
    <message>
        <source>Choose the classes and the subtree</source>
        <translation>בחירת המחלקות ותת־העץ</translation>
    </message>
    <message>
        <source>Editors</source>
        <translation>עורכים</translation>
    </message>
    <message>
        <source>Gives the role %role the policies changeclass/convert (change the class of an object they may edit) and changeclass/restore (undo such a change), without limitations: the role&apos;s assignment limits them as it limits the rest of the role. Nothing is added twice; Remove takes the role&apos;s changeclass policies away again.</source>
        <translation>מעניק לתפקיד %role את המדיניות changeclass/convert (שינוי המחלקה של אובייקט שמותר לערוך) ו־changeclass/restore (ביטול שינוי כזה), ללא הגבלות: שיוך התפקיד מגביל אותן כפי שהוא מגביל את שאר התפקיד. דבר אינו נוסף פעמיים; הסרה מוציאה שוב את מדיניות changeclass מהתפקיד.</translation>
    </message>
    <message>
        <source>held</source>
        <translation>קיים</translation>
    </message>
    <message>
        <source>not held</source>
        <translation>לא קיים</translation>
    </message>
    <message>
        <source>Both buttons ask you to confirm it is you. The same from a shell: ./console exp:addchangeclasspolicies --apply (dry run without --apply).</source>
        <translation>שני הכפתורים מבקשים לאמת את זהותך. אותו הדבר משורת הפקודה: ./console exp:addchangeclasspolicies --apply (הרצת ניסיון ללא --apply).</translation>
    </message>
    <message>
        <source>Allow editors to change classes</source>
        <translation>לאפשר לעורכים לשנות מחלקות</translation>
    </message>
    <message>
        <source>Remove</source>
        <translation>הסרה</translation>
    </message>
    <message>
        <source>There is no role %role on this installation. Give the policies to another role with ./console exp:addchangeclasspolicies --role=&lt;name&gt; --apply.</source>
        <translation>אין תפקיד %role בהתקנה זו. העניקו את המדיניות לתפקיד אחר באמצעות ./console exp:addchangeclasspolicies --role=&lt;name&gt; --apply.</translation>
    </message>
    <message>
        <source>From a shell</source>
        <translation>משורת הפקודה</translation>
    </message>
    <message>
        <source>Change %name from %class into %new_class</source>
        <translation>שינוי %name מהמחלקה %class ל־%new_class</translation>
    </message>
    <message>
        <source>Nothing is written until you apply the change. Then a restore point of every version and language is taken first, the change runs in one transaction that is rolled back as a whole on any error, and the object&apos;s caches and search index entry are renewed.</source>
        <translation>דבר אינו נכתב עד שתחילו את השינוי. אז נלקחת תחילה נקודת שחזור של כל גרסה ושפה, השינוי רץ בטרנזקציה אחת שמוחזרת לאחור במלואה בכל שגיאה, והמטמונים של האובייקט ורשומתו באינדקס החיפוש מתחדשים.</translation>
    </message>
    <message>
        <source>What the change touches</source>
        <translation>במה נוגע השינוי</translation>
    </message>
    <message>
        <source>versions</source>
        <translation>גרסאות</translation>
    </message>
    <message>
        <source>languages</source>
        <translation>שפות</translation>
    </message>
    <message>
        <source>attributes carried over</source>
        <translation>מאפיינים שהועברו</translation>
    </message>
    <message>
        <source>old attributes dropped</source>
        <translation>מאפיינים ישנים שהושמטו</translation>
    </message>
    <message>
        <source>drafts</source>
        <translation>טיוטות</translation>
    </message>
    <message>
        <source>Without --apply the command only shows this preview. Add --apply to change the class; --format=json prints the plan for scripts.</source>
        <translation>ללא --apply הפקודה רק מציגה תצוגה מקדימה זו. הוסיפו --apply כדי לשנות את המחלקה; --format=json מדפיס את התוכנית עבור סקריפטים.</translation>
    </message>
    <message>
        <source>Changed a select? Update the preview first: the change applies exactly what the preview shows.</source>
        <translation>שיניתם בחירה? עדכנו קודם את התצוגה המקדימה: השינוי מחיל בדיוק את מה שהתצוגה המקדימה מציגה.</translation>
    </message>
    <message>
        <source>Fix what is listed at the top before the class can be changed.</source>
        <translation>תקנו את מה שמפורט למעלה לפני שניתן יהיה לשנות את המחלקה.</translation>
    </message>
    <message>
        <source>Back</source>
        <translation>חזרה</translation>
    </message>
    <message>
        <source>Cancel</source>
        <translation>ביטול</translation>
    </message>
    <message>
        <source>Change the class</source>
        <translation>שינוי המחלקה</translation>
    </message>
    <message>
        <source>Class change of %name</source>
        <translation>שינוי מחלקה של %name</translation>
    </message>
    <message>
        <source>%name is of the class %class now. Every version and language was changed in one transaction.</source>
        <translation>%name שייך כעת למחלקה %class. כל גרסה ושפה שונו בטרנזקציה אחת.</translation>
    </message>
    <message>
        <source>The change was rolled back: nothing was changed, the content is exactly as it was.</source>
        <translation>השינוי הוחזר לאחור: דבר לא שונה, התוכן בדיוק כפי שהיה.</translation>
    </message>
    <message>
        <source>This change was undone: %name is of the class %class again.</source>
        <translation>שינוי זה בוטל: %name שייך שוב למחלקה %class.</translation>
    </message>
    <message>
        <source>The restore point of this change was discarded; the change can no longer be undone from here.</source>
        <translation>נקודת השחזור של שינוי זה נזנחה; לא ניתן עוד לבטל את השינוי מכאן.</translation>
    </message>
    <message>
        <source>This change has not finished. If it does not finish within a minute, it was interrupted before it was committed and nothing was changed.</source>
        <translation>שינוי זה לא הסתיים. אם אינו מסתיים תוך דקה, הוא נקטע לפני שאושר ודבר לא שונה.</translation>
    </message>
    <message>
        <source>The change</source>
        <translation>השינוי</translation>
    </message>
    <message>
        <source>Object ID %id</source>
        <translation>מזהה אובייקט %id</translation>
    </message>
    <message>
        <source>From</source>
        <translation>מ־</translation>
    </message>
    <message>
        <source>Into</source>
        <translation>אל</translation>
    </message>
    <message>
        <source>Versions</source>
        <translation>גרסאות</translation>
    </message>
    <message>
        <source>Restore point</source>
        <translation>נקודת שחזור</translation>
    </message>
    <message>
        <source>%rows attribute rows kept</source>
        <translation>שורות מאפיינים שנשמרו: %rows</translation>
    </message>
    <message>
        <source>Mapping used</source>
        <translation>המיפוי ששימש</translation>
    </message>
    <message>
        <source>New attribute</source>
        <translation>מאפיין חדש</translation>
    </message>
    <message>
        <source>Took its content from</source>
        <translation>קיבל את תוכנו מ־</translation>
    </message>
    <message>
        <source>(empty)</source>
        <translation>(ריק)</translation>
    </message>
    <message>
        <source>Undo</source>
        <translation>ביטול השינוי</translation>
    </message>
    <message>
        <source>Restoring puts every attribute row of every version and language back as it was before the change, and the class %class with it, in one transaction.</source>
        <translation>השחזור מחזיר בטרנזקציה אחת כל שורת מאפיין של כל גרסה ושפה למצבה לפני השינוי, ואיתן את המחלקה %class.</translation>
    </message>
    <message>
        <source>Restore the object…</source>
        <translation>שחזור האובייקט…</translation>
    </message>
    <message>
        <source>Discard the restore point…</source>
        <translation>זניחת נקודת השחזור…</translation>
    </message>
    <message>
        <source>The content of the dropped attributes (and their files) is deleted for good, and the change can no longer be undone.</source>
        <translation>התוכן של המאפיינים שהושמטו (והקבצים שלהם) נמחק לצמיתות, ולא ניתן עוד לבטל את השינוי.</translation>
    </message>
    <message>
        <source>Discard</source>
        <translation>זניחה</translation>
    </message>
    <message>
        <source>Next</source>
        <translation>הבא</translation>
    </message>
    <message>
        <source>Open %name</source>
        <translation>פתיחת %name</translation>
    </message>
    <message>
        <source>Change the other %count objects of %class the same way, in the background</source>
        <translation>שינוי %count האובייקטים האחרים של %class באותו אופן, ברקע</translation>
    </message>
    <message>
        <source>All class changes</source>
        <translation>כל שינויי המחלקה</translation>
    </message>
    <message>
        <source>From a shell:</source>
        <translation>משורת הפקודה:</translation>
    </message>
    <message>
        <source>Change the class of %name</source>
        <translation>שינוי המחלקה של %name</translation>
    </message>
    <message>
        <source>The object keeps its id, its locations, its versions and its languages; only its class changes. The next step shows exactly what happens to each attribute before anything is written, and a restore point lets you undo the change.</source>
        <translation>האובייקט שומר על המזהה, המיקומים, הגרסאות והשפות שלו; רק המחלקה שלו משתנה. השלב הבא מראה בדיוק מה קורה לכל מאפיין לפני שדבר נכתב, ונקודת שחזור מאפשרת לבטל את השינוי.</translation>
    </message>
    <message>
        <source>The object now</source>
        <translation>האובייקט כעת</translation>
    </message>
    <message>
        <source>Name</source>
        <translation>שם</translation>
    </message>
    <message>
        <source>Class</source>
        <translation>מחלקה</translation>
    </message>
    <message>
        <source>Object ID</source>
        <translation>מזהה אובייקט</translation>
    </message>
    <message>
        <source>Languages</source>
        <translation>שפות</translation>
    </message>
    <message>
        <source>Sub-items</source>
        <translation>פריטי משנה</translation>
    </message>
    <message>
        <source>Objects relating to it</source>
        <translation>אובייקטים הקשורים אליו</translation>
    </message>
    <message>
        <source>Modified</source>
        <translation>שונה</translation>
    </message>
    <message>
        <source>Attributes of %class (%count)</source>
        <translation>המאפיינים של %class (%count)</translation>
    </message>
    <message>
        <source>The new class</source>
        <translation>המחלקה החדשה</translation>
    </message>
    <message>
        <source>Other</source>
        <translation>אחר</translation>
    </message>
    <message>
        <source>%count classes are left out: your role does not allow changing this object into them.</source>
        <translation>%count מחלקות הושמטו: התפקיד שלך אינו מאפשר לשנות אובייקט זה אליהן.</translation>
    </message>
    <message>
        <source>Your role does not allow changing this object into any class (policy changeclass/convert).</source>
        <translation>התפקיד שלך אינו מאפשר לשנות אובייקט זה לשום מחלקה (מדיניות changeclass/convert).</translation>
    </message>
    <message>
        <source>Nothing is changed yet.</source>
        <translation>דבר עדיין לא שונה.</translation>
    </message>
    <message>
        <source>Continue to the mapping</source>
        <translation>המשך למיפוי</translation>
    </message>
    <message>
        <source>Earlier class changes of this object</source>
        <translation>שינויי מחלקה קודמים של אובייקט זה</translation>
    </message>
    <message>
        <source>Carried over</source>
        <translation>הועבר</translation>
    </message>
    <message>
        <source>Carried over, other datatype</source>
        <translation>הועבר, סוג נתונים אחר</translation>
    </message>
    <message>
        <source>Converted</source>
        <translation>הומר</translation>
    </message>
    <message>
        <source>Copied</source>
        <translation>הועתק</translation>
    </message>
    <message>
        <source>Stays empty</source>
        <translation>נשאר ריק</translation>
    </message>
    <message>
        <source>Not possible</source>
        <translation>לא אפשרי</translation>
    </message>
    <message>
        <source>Attributes of the new class</source>
        <translation>המאפיינים של המחלקה החדשה</translation>
    </message>
    <message>
        <source>Choose for each attribute of the new class the attribute of the old class whose content it takes. Only attributes whose content can be carried into it are offered.</source>
        <translation>בחרו לכל מאפיין של המחלקה החדשה את המאפיין של המחלקה הישנה שאת תוכנו הוא מקבל. מוצעים רק מאפיינים שאת תוכנם ניתן להעביר אליו.</translation>
    </message>
    <message>
        <source>Takes its content from</source>
        <translation>מקבל את תוכנו מ־</translation>
    </message>
    <message>
        <source>How</source>
        <translation>אופן</translation>
    </message>
    <message>
        <source>Content now</source>
        <translation>התוכן כעת</translation>
    </message>
    <message>
        <source>Required</source>
        <translation>חובה</translation>
    </message>
    <message>
        <source>(leave empty)</source>
        <translation>(להשאיר ריק)</translation>
    </message>
    <message>
        <source>Old attributes nothing takes over</source>
        <translation>מאפיינים ישנים ששום דבר אינו מקבל</translation>
    </message>
    <message>
        <source>Their content is no longer shown after the change. It is not deleted: the restore point keeps it until it is discarded.</source>
        <translation>לאחר השינוי תוכנם כבר אינו מוצג. הוא אינו נמחק: נקודת השחזור שומרת אותו עד שהיא נזנחת.</translation>
    </message>
    <message>
        <source>Old attribute</source>
        <translation>מאפיין ישן</translation>
    </message>
    <message>
        <source>Datatype</source>
        <translation>סוג נתונים</translation>
    </message>
    <message>
        <source>(empty in every language)</source>
        <translation>(ריק בכל השפות)</translation>
    </message>
    <message>
        <source>Every old attribute is taken over: nothing is dropped.</source>
        <translation>כל מאפיין ישן מועבר: דבר אינו מושמט.</translation>
    </message>
    <message>
        <source>The class cannot be changed yet:</source>
        <translation>עדיין לא ניתן לשנות את המחלקה:</translation>
    </message>
    <message>
        <source>Before you apply the change:</source>
        <translation>לפני החלת השינוי:</translation>
    </message>
    <message>
        <source>Running</source>
        <translation>פועל</translation>
    </message>
    <message>
        <source>Steps</source>
        <translation>שלבים</translation>
    </message>
    <message>
        <source>Choose the new class</source>
        <translation>בחירת המחלקה החדשה</translation>
    </message>
    <message>
        <source>Map the attributes and preview</source>
        <translation>מיפוי המאפיינים ותצוגה מקדימה</translation>
    </message>
    <message>
        <source>Result and restore point</source>
        <translation>תוצאה ונקודת שחזור</translation>
    </message>
    <message>
        <source>The object is of the class %class already.</source>
        <translation>האובייקט כבר שייך למחלקה %class.</translation>
    </message>
    <message>
        <source>The object is in the trash. Restore it before changing its class.</source>
        <translation>האובייקט נמצא בסל המחזור. שחזרו אותו לפני שינוי המחלקה שלו.</translation>
    </message>
    <message>
        <source>The object is a user account. The class of user accounts cannot be changed here.</source>
        <translation>האובייקט הוא חשבון משתמש. לא ניתן לשנות כאן את המחלקה של חשבונות משתמש.</translation>
    </message>
    <message>
        <source>The object is locked. Unlock it before changing its class.</source>
        <translation>האובייקט נעול. בטלו את הנעילה לפני שינוי המחלקה שלו.</translation>
    </message>
    <message>
        <source>You may not edit this object, so you may not change its class either.</source>
        <translation>אין לך הרשאה לערוך אובייקט זה, ולכן גם לא לשנות את המחלקה שלו.</translation>
    </message>
    <message>
        <source>A background job works on this part of the tree (%job). Wait until it is done.</source>
        <translation>משימת רקע פועלת על חלק זה של העץ (%job). המתינו עד שתסתיים.</translation>
    </message>
    <message>
        <source>The object has no versions or no languages to change.</source>
        <translation>לאובייקט אין גרסאות או שפות לשינוי.</translation>
    </message>
    <message>
        <source>The class %class has no attributes.</source>
        <translation>למחלקה %class אין מאפיינים.</translation>
    </message>
    <message>
        <source>The class %class has an attribute of the datatype %datatype (%attribute), which cannot be changed into.</source>
        <translation>למחלקה %class יש מאפיין מסוג הנתונים %datatype (%attribute), שלא ניתן לשנות אליו.</translation>
    </message>
    <message>
        <source>The attribute %attribute is of the datatype %datatype, which cannot be changed from.</source>
        <translation>המאפיין %attribute הוא מסוג הנתונים %datatype, שלא ניתן לשנות ממנו.</translation>
    </message>
    <message>
        <source>The class %class has no attribute %identifier.</source>
        <translation>למחלקה %class אין מאפיין %identifier.</translation>
    </message>
    <message>
        <source>%source (%source_datatype) cannot be carried into %destination (%destination_datatype).</source>
        <translation>לא ניתן להעביר את %source (%source_datatype) אל %destination (%destination_datatype).</translation>
    </message>
    <message>
        <source>%source is carried into %destination as well: a copy of the current version&apos;s content.</source>
        <translation>%source מועבר גם אל %destination: עותק של תוכן הגרסה הנוכחית.</translation>
    </message>
    <message>
        <source>%destination is required and stays empty: the next edit asks for a value before it can be published.</source>
        <translation>%destination הוא חובה ונשאר ריק: העריכה הבאה תבקש ערך לפני שניתן יהיה לפרסם.</translation>
    </message>
    <message>
        <source>%source holds content in %languages that the new class does not take over. It is kept in the restore point until that is discarded.</source>
        <translation>%source מכיל תוכן בשפות %languages שהמחלקה החדשה אינה מקבלת. הוא נשמר בנקודת השחזור עד שהיא נזנחת.</translation>
    </message>
    <message>
        <source>The object has %count sub-items, but %class is no container: they stay, but no new ones can be added below it.</source>
        <translation>לאובייקט יש %count פריטי משנה, אך %class אינה מכל: הם נשארים, אך לא ניתן להוסיף תחתיו חדשים.</translation>
    </message>
    <message>
        <source>%count drafts of other users are changed too. Tell them before they publish an edit that was open during the change.</source>
        <translation>גם %count טיוטות של משתמשים אחרים משתנות. הודיעו להם לפני שהם מפרסמים עריכה שהייתה פתוחה במהלך השינוי.</translation>
    </message>
    <message>
        <source>%count drafts are changed too.</source>
        <translation>גם %count טיוטות משתנות.</translation>
    </message>
    <message>
        <source>%count objects relate to this one through an attribute. A relation attribute that only accepts certain classes may no longer accept it.</source>
        <translation>%count אובייקטים קשורים לאובייקט זה דרך מאפיין. מאפיין קשר שמקבל רק מחלקות מסוימות עשוי שלא לקבל אותו עוד.</translation>
    </message>
    <message>
        <source>The object no longer exists.</source>
        <translation>האובייקט כבר אינו קיים.</translation>
    </message>
    <message>
        <source>The object is no longer of the class it was changed into.</source>
        <translation>האובייקט כבר אינו שייך למחלקה שאליה שונה.</translation>
    </message>
    <message>
        <source>The object was edited after the change: restoring would lose that work. Change its class back instead.</source>
        <translation>האובייקט נערך לאחר השינוי: שחזור יאבד את העבודה הזו. במקום זאת שנו את המחלקה שלו בחזרה.</translation>
    </message>
    <message>
        <source>The old class no longer exists.</source>
        <translation>המחלקה הישנה כבר אינה קיימת.</translation>
    </message>
    <message>
        <source>The old class lost an attribute since the change.</source>
        <translation>המחלקה הישנה איבדה מאפיין מאז השינוי.</translation>
    </message>
    <message>
        <source>Your role does not allow changing %class into %new_class here (policy changeclass/convert).</source>
        <translation>התפקיד שלך אינו מאפשר כאן לשנות את %class ל־%new_class (מדיניות changeclass/convert).</translation>
    </message>
    <message>
        <source>The object or the mapping changed since the preview. Check the new preview and apply again.</source>
        <translation>האובייקט או המיפוי השתנו מאז התצוגה המקדימה. בדקו את התצוגה המקדימה החדשה והחילו שוב.</translation>
    </message>
    <message>
        <source>The restore point could not be written to %dir, so nothing was changed.</source>
        <translation>לא ניתן היה לכתוב את נקודת השחזור ל־%dir, ולכן דבר לא שונה.</translation>
    </message>
    <message>
        <source>The change was rolled back, nothing was changed: %reason</source>
        <translation>השינוי הוחזר לאחור, דבר לא שונה: %reason</translation>
    </message>
    <message>
        <source>The class was changed.</source>
        <translation>המחלקה שונתה.</translation>
    </message>
    <message>
        <source>The class was not changed. The content is exactly as it was.</source>
        <translation>המחלקה לא שונתה. התוכן בדיוק כפי שהיה.</translation>
    </message>
    <message>
        <source>Your role does not allow changing %class into %new_class here (policy changeclass/bulk).</source>
        <translation>התפקיד שלך אינו מאפשר כאן לשנות את %class ל־%new_class (מדיניות changeclass/bulk).</translation>
    </message>
    <message>
        <source>There is no published object of the class %class below this node.</source>
        <translation>אין אובייקט שפורסם של המחלקה %class מתחת לצומת זה.</translation>
    </message>
    <message>
        <source>Nothing was started: check the new preview.</source>
        <translation>דבר לא הופעל: בדקו את התצוגה המקדימה החדשה.</translation>
    </message>
    <message>
        <source>The job was not started.</source>
        <translation>המשימה לא הופעלה.</translation>
    </message>
    <message>
        <source>There is no role %role.</source>
        <translation>אין תפקיד %role.</translation>
    </message>
    <message>
        <source>The role %role may change classes now: %policies.</source>
        <translation>התפקיד %role רשאי כעת לשנות מחלקות: %policies.</translation>
    </message>
    <message>
        <source>The role %role held these policies already; nothing was added.</source>
        <translation>לתפקיד %role כבר הייתה מדיניות זו; דבר לא נוסף.</translation>
    </message>
    <message>
        <source>The role %role may no longer change classes: %policies removed.</source>
        <translation>התפקיד %role כבר אינו רשאי לשנות מחלקות: %policies הוסרו.</translation>
    </message>
    <message>
        <source>The role %role had no class change policies; nothing was removed.</source>
        <translation>לתפקיד %role לא הייתה מדיניות שינוי מחלקה; דבר לא הוסר.</translation>
    </message>
    <message>
        <source>Preview the change before applying it.</source>
        <translation>הציגו תצוגה מקדימה של השינוי לפני החלתו.</translation>
    </message>
    <message>
        <source>Nothing was changed: check the new preview.</source>
        <translation>דבר לא שונה: בדקו את התצוגה המקדימה החדשה.</translation>
    </message>
    <message>
        <source>Map the attributes</source>
        <translation>מיפוי המאפיינים</translation>
    </message>
    <message>
        <source>Result</source>
        <translation>תוצאה</translation>
    </message>
    <message>
        <source>Your role does not allow undoing this change (policy changeclass/restore).</source>
        <translation>התפקיד שלך אינו מאפשר לבטל שינוי זה (מדיניות changeclass/restore).</translation>
    </message>
    <message>
        <source>The change was not undone.</source>
        <translation>השינוי לא בוטל.</translation>
    </message>
    <message>
        <source>The object is of the class %class again, with all its attributes as they were.</source>
        <translation>האובייקט שייך שוב למחלקה %class, עם כל המאפיינים כפי שהיו.</translation>
    </message>
    <message>
        <source>This restore point cannot be discarded.</source>
        <translation>לא ניתן לזנוח נקודת שחזור זו.</translation>
    </message>
    <message>
        <source>The restore point was discarded: the dropped data is gone for good.</source>
        <translation>נקודת השחזור נזנחה: הנתונים שהושמטו אבדו לצמיתות.</translation>
    </message>
    <message>
        <source>Your role does not allow changing this object into that class (policy changeclass/convert).</source>
        <translation>התפקיד שלך אינו מאפשר לשנות אובייקט זה למחלקה זו (מדיניות changeclass/convert).</translation>
    </message>
    <message>
        <source>This change is pending, there is nothing to restore.</source>
        <translation>שינוי זה ממתין, אין מה לשחזר.</translation>
    </message>
    <message>
        <source>This change is restored, there is nothing to restore.</source>
        <translation>שינוי זה כבר שוחזר, אין מה לשחזר.</translation>
    </message>
    <message>
        <source>This change is discarded, there is nothing to restore.</source>
        <translation>שינוי זה נזנח, אין מה לשחזר.</translation>
    </message>
    <message>
        <source>This change is failed, there is nothing to restore.</source>
        <translation>שינוי זה נכשל, אין מה לשחזר.</translation>
    </message>
</context>
</TS>
