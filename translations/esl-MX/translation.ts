<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE TS>
<TS version="2.0" language="es_MX">
<context>
    <name>design/admin/changeclass/action</name>
    <message>
        <source>Convert Instructions for converting class &apos;%source_class_name&apos; to &apos;%dest_class_name&apos;</source>
        <translation>Convertir: instrucciones para convertir la clase &apos;%source_class_name&apos; a &apos;%dest_class_name&apos;</translation>
    </message>
    <message>
        <source>&lt;b&gt;The object was NOT converted.&lt;/b&gt; Nothing was changed; the content is exactly as it was.</source>
        <translation>&lt;b&gt;El objeto NO se convirtió.&lt;/b&gt; No se cambió nada; el contenido está exactamente como estaba.</translation>
    </message>
    <message>
        <source>Reason:</source>
        <translation>Motivo:</translation>
    </message>
    <message>
        <source>The object was converted to &apos;%dest_class_name&apos;.</source>
        <translation>El objeto se convirtió a &apos;%dest_class_name&apos;.</translation>
    </message>
    <message>
        <source>A file with all params for this convertion has been created in &apos;%convert_file_path&apos; and can be used for batch convertions of all remaining (%source_object_count objects) &apos;%source_class_name&apos; to class &apos;%dest_class_name&apos;.</source>
        <translation>Se creó un archivo con todos los parámetros de esta conversión en &apos;%convert_file_path&apos;; puede usarse para convertir por lotes todos los objetos restantes (%source_object_count objetos) de &apos;%source_class_name&apos; a la clase &apos;%dest_class_name&apos;.</translation>
    </message>
    <message>
        <source>&lt;b&gt;ERROR:&lt;/b&gt; eZFile could not store parameter file!</source>
        <translation>&lt;b&gt;ERROR:&lt;/b&gt; ¡eZFile no pudo almacenar el archivo de parámetros!</translation>
    </message>
    <message>
        <source>Do you have write access to path (%convert_file_path)?</source>
        <translation>¿Tienes acceso de escritura a la ruta (%convert_file_path)?</translation>
    </message>
    <message>
        <source>Command line command for running this script would be like this:</source>
        <translation>El comando de línea de comandos para ejecutar este script sería algo así:</translation>
    </message>
    <message>
        <source>Notice:</source>
        <translation>Aviso:</translation>
    </message>
    <message>
        <source>* Switch &apos;example&apos; with the name of your siteaccess, preferably the one you are using right now!&lt;br /&gt;&amp;nbsp; ( so your sure it uses same db and cache folder )</source>
        <translation>* Sustituye &apos;example&apos; por el nombre de tu acceso al sitio, de preferencia el que estás usando ahora mismo.&lt;br /&gt;&amp;nbsp; ( así te aseguras de que use la misma base de datos y la misma carpeta de caché )</translation>
    </message>
    <message>
        <source>Content of convert &apos;paramfile&apos; (%convert_file_name):</source>
        <translation>Contenido del &apos;paramfile&apos; de conversión (%convert_file_name):</translation>
    </message>
    <message>
        <source>Continue to new object</source>
        <translation>Continuar al nuevo objeto</translation>
    </message>
</context>
<context>
    <name>design/admin/changeclass/map_attributes</name>
    <message>
        <source>Change object content class &amp;lt;%name&amp;gt; [%class_name]</source>
        <translation>Cambiar la clase de contenido del objeto &amp;lt;%name&amp;gt; [%class_name]</translation>
    </message>
    <message>
        <source>Warnings:</source>
        <translation>Advertencias:</translation>
    </message>
    <message>
        <source>Destination class has at least one unsupported datatype. You can&apos;t use this class.</source>
        <translation>La clase de destino tiene al menos un tipo de datos no compatible. No puedes usar esta clase.</translation>
    </message>
    <message>
        <source>Destination class is not configured to be container!</source>
        <translation>¡La clase de destino no está configurada como contenedor!</translation>
    </message>
    <message>
        <source>The following attributes can not be mapped:</source>
        <translation>Los siguientes atributos no se pueden asignar:</translation>
    </message>
    <message>
        <source>No warnigs for this operation</source>
        <translation>No hay advertencias para esta operación</translation>
    </message>
    <message>
        <source>Back to destination class selection</source>
        <translation>Regresar a la selección de la clase de destino</translation>
    </message>
    <message>
        <source>Attributes mapping</source>
        <translation>Asignación de atributos</translation>
    </message>
    <message>
        <source>Source class (%source_class)</source>
        <translation>Clase de origen (%source_class)</translation>
    </message>
    <message>
        <source>Destination class (%destination_class)</source>
        <translation>Clase de destino (%destination_class)</translation>
    </message>
    <message>
        <source>(leave empty)</source>
        <translation>(dejar vacío)</translation>
    </message>
    <message>
        <source>Options:</source>
        <translation>Opciones:</translation>
    </message>
    <message>
        <source>Keep original object creator and creation date</source>
        <translation>Conservar el creador y la fecha de creación originales del objeto</translation>
    </message>
    <message>
        <source>Add source object&apos;s children to new object</source>
        <translation>Añadir los hijos del objeto de origen al nuevo objeto</translation>
    </message>
    <message>
        <source>Generate parameters for converting all instancees of this class</source>
        <translation>Generar parámetros para convertir todas las instancias de esta clase</translation>
    </message>
    <message>
        <source>Modify Object</source>
        <translation>Modificar objeto</translation>
    </message>
</context>
<context>
    <name>design/admin/changeclass/select_class</name>
    <message>
        <source>Change object content class &amp;lt;%name&amp;gt; [%class_name]</source>
        <translation>Cambiar la clase de contenido del objeto &amp;lt;%name&amp;gt; [%class_name]</translation>
    </message>
    <message>
        <source>General object&apos;s informations</source>
        <translation>Información general del objeto</translation>
    </message>
    <message>
        <source>Content class name:</source>
        <translation>Nombre de la clase de contenido:</translation>
    </message>
    <message>
        <source>Children count:</source>
        <translation>Número de hijos:</translation>
    </message>
    <message>
        <source>Languages:</source>
        <translation>Idiomas:</translation>
    </message>
    <message>
        <source>Current version:</source>
        <translation>Versión actual:</translation>
    </message>
    <message>
        <source>Related objects [%count]:</source>
        <translation>Objetos relacionados [%count]:</translation>
    </message>
    <message>
        <source>no objects</source>
        <translation>ningún objeto</translation>
    </message>
    <message>
        <source>Reverse related objects [%count]:</source>
        <translation>Objetos que hacen referencia a este [%count]:</translation>
    </message>
    <message>
        <source>Attributes (datatype):</source>
        <translation>Atributos (tipo de datos):</translation>
    </message>
    <message>
        <source>Destination content class:</source>
        <translation>Clase de contenido de destino:</translation>
    </message>
    <message>
        <source>%identifier (unnamed)</source>
        <translation>%identifier (sin nombre)</translation>
    </message>
    <message>
        <source>Select</source>
        <translation>Seleccionar</translation>
    </message>
</context>
<context>
    <name>design/admin/changeclass</name>
    <message>
        <source>Change content class</source>
        <translation>Cambiar clase de contenido</translation>
    </message>
</context>
<context>
    <name>extension/expchangeclass</name>
    <message>
        <source>Change many objects</source>
        <translation>Cambiar muchos objetos</translation>
    </message>
    <message>
        <source>Every published object of a class below a node, changed with one mapping in the background. You can leave the progress page; each object gets its own check, restore point and transaction, and one that cannot be changed is skipped with the reason.</source>
        <translation>Todos los objetos publicados de una clase bajo un nodo, cambiados con una sola asignación en segundo plano. Puedes salir de la página de progreso; cada objeto tiene su propia verificación, su punto de restauración y su transacción, y el que no se puede cambiar se omite indicando el motivo.</translation>
    </message>
    <message>
        <source>Background jobs need Exponential 6.0.15 or later. Use the command shown below.</source>
        <translation>Los trabajos en segundo plano requieren Exponential 6.0.15 o posterior. Usa el comando que se muestra abajo.</translation>
    </message>
    <message>
        <source>Classes and subtree</source>
        <translation>Clases y subárbol</translation>
    </message>
    <message>
        <source>Objects of the class</source>
        <translation>Objetos de la clase</translation>
    </message>
    <message>
        <source>Change into</source>
        <translation>Cambiar a</translation>
    </message>
    <message>
        <source>Below the node (ID)</source>
        <translation>Bajo el nodo (ID)</translation>
    </message>
    <message>
        <source>Show the preview</source>
        <translation>Mostrar la vista previa</translation>
    </message>
    <message>
        <source>%count objects of %class below %node</source>
        <translation>%count objetos de %class bajo %node</translation>
    </message>
    <message>
        <source>preview this one</source>
        <translation>ver la vista previa de este</translation>
    </message>
    <message>
        <source>and %count more</source>
        <translation>y %count más</translation>
    </message>
    <message>
        <source>The same from a shell</source>
        <translation>Lo mismo desde una terminal</translation>
    </message>
    <message>
        <source>Changed a select? Update the preview first: the job applies exactly what the preview shows.</source>
        <translation>¿Cambiaste una selección? Primero actualiza la vista previa: el trabajo aplica exactamente lo que muestra la vista previa.</translation>
    </message>
    <message>
        <source>Update the preview</source>
        <translation>Actualizar la vista previa</translation>
    </message>
    <message>
        <source>Start the background job</source>
        <translation>Iniciar el trabajo en segundo plano</translation>
    </message>
    <message>
        <source>Change class</source>
        <translation>Cambiar clase</translation>
    </message>
    <message>
        <source>Change the class of content that exists: an article that should be a blog post, a folder that should be a landing page. Open the context menu of an item in the content tree or the sub-items list and choose &quot;Change content class&quot;. You see what happens to each attribute before anything is written, and every change keeps a restore point to undo it.</source>
        <translation>Cambia la clase de un contenido existente: un artículo que debería ser una entrada de blog, una carpeta que debería ser una página de inicio. Abre el menú contextual de un elemento en el árbol de contenido o en la lista de subelementos y elige &quot;Cambiar la clase de contenido&quot;. Ves lo que pasa con cada atributo antes de que se escriba nada, y cada cambio conserva un punto de restauración para deshacerlo.</translation>
    </message>
    <message>
        <source>restorable changes</source>
        <translation>cambios restaurables</translation>
    </message>
    <message>
        <source>undone</source>
        <translation>deshechos</translation>
    </message>
    <message>
        <source>restore points discarded</source>
        <translation>puntos de restauración descartados</translation>
    </message>
    <message>
        <source>rolled back</source>
        <translation>revertidos</translation>
    </message>
    <message>
        <source>Recent class changes</source>
        <translation>Cambios de clase recientes</translation>
    </message>
    <message>
        <source>Your recent class changes</source>
        <translation>Tus cambios de clase recientes</translation>
    </message>
    <message>
        <source>Filter</source>
        <translation>Filtrar</translation>
    </message>
    <message>
        <source>State</source>
        <translation>Estado</translation>
    </message>
    <message>
        <source>Every state</source>
        <translation>Todos los estados</translation>
    </message>
    <message>
        <source>Changed, restorable</source>
        <translation>Cambiado, restaurable</translation>
    </message>
    <message>
        <source>Undone</source>
        <translation>Deshecho</translation>
    </message>
    <message>
        <source>Restore point discarded</source>
        <translation>Punto de restauración descartado</translation>
    </message>
    <message>
        <source>Rolled back</source>
        <translation>Revertido</translation>
    </message>
    <message>
        <source>When</source>
        <translation>Cuándo</translation>
    </message>
    <message>
        <source>Object</source>
        <translation>Objeto</translation>
    </message>
    <message>
        <source>Change</source>
        <translation>Cambio</translation>
    </message>
    <message>
        <source>By</source>
        <translation>Por</translation>
    </message>
    <message>
        <source>Actions</source>
        <translation>Acciones</translation>
    </message>
    <message>
        <source>Restore…</source>
        <translation>Restaurar…</translation>
    </message>
    <message>
        <source>%name becomes %class again, with its attributes as they were.</source>
        <translation>%name vuelve a ser %class, con sus atributos tal como estaban.</translation>
    </message>
    <message>
        <source>Restore</source>
        <translation>Restaurar</translation>
    </message>
    <message>
        <source>No class changes yet.</source>
        <translation>Todavía no hay cambios de clase.</translation>
    </message>
    <message>
        <source>Restore points are kept in %directory until they are discarded or purged (./console exp:changeclass --purge=&lt;days&gt; --apply).</source>
        <translation>Los puntos de restauración se conservan en %directory hasta que se descartan o se purgan (./console exp:changeclass --purge=&lt;days&gt; --apply).</translation>
    </message>
    <message>
        <source>Every object of a class in a part of the tree, with one mapping, as a background job with a progress page. Each object gets its own preview check, restore point and transaction; an object that cannot be changed is skipped with the reason.</source>
        <translation>Todos los objetos de una clase en una parte del árbol, con una sola asignación, como trabajo en segundo plano con página de progreso. Cada objeto tiene su propia verificación de vista previa, su punto de restauración y su transacción; un objeto que no se puede cambiar se omite indicando el motivo.</translation>
    </message>
    <message>
        <source>Choose the classes and the subtree</source>
        <translation>Elegir las clases y el subárbol</translation>
    </message>
    <message>
        <source>Editors</source>
        <translation>Editores</translation>
    </message>
    <message>
        <source>Gives the role %role the policies changeclass/convert (change the class of an object they may edit) and changeclass/restore (undo such a change), without limitations: the role&apos;s assignment limits them as it limits the rest of the role. Nothing is added twice; Remove takes the role&apos;s changeclass policies away again.</source>
        <translation>Otorga al rol %role las políticas changeclass/convert (cambiar la clase de un objeto que puede editar) y changeclass/restore (deshacer ese cambio), sin limitaciones: la asignación del rol las limita igual que limita el resto del rol. Nada se agrega dos veces; Eliminar le quita de nuevo al rol las políticas changeclass.</translation>
    </message>
    <message>
        <source>held</source>
        <translation>otorgada</translation>
    </message>
    <message>
        <source>not held</source>
        <translation>no otorgada</translation>
    </message>
    <message>
        <source>Both buttons ask you to confirm it is you. The same from a shell: ./console exp:addchangeclasspolicies --apply (dry run without --apply).</source>
        <translation>Ambos botones te piden que confirmes que eres tú. Lo mismo desde una terminal: ./console exp:addchangeclasspolicies --apply (simulación sin --apply).</translation>
    </message>
    <message>
        <source>Allow editors to change classes</source>
        <translation>Permitir a los editores cambiar clases</translation>
    </message>
    <message>
        <source>Remove</source>
        <translation>Eliminar</translation>
    </message>
    <message>
        <source>There is no role %role on this installation. Give the policies to another role with ./console exp:addchangeclasspolicies --role=&lt;name&gt; --apply.</source>
        <translation>No existe el rol %role en esta instalación. Otorga las políticas a otro rol con ./console exp:addchangeclasspolicies --role=&lt;name&gt; --apply.</translation>
    </message>
    <message>
        <source>From a shell</source>
        <translation>Desde una terminal</translation>
    </message>
    <message>
        <source>Change %name from %class into %new_class</source>
        <translation>Cambiar %name de %class a %new_class</translation>
    </message>
    <message>
        <source>Nothing is written until you apply the change. Then a restore point of every version and language is taken first, the change runs in one transaction that is rolled back as a whole on any error, and the object&apos;s caches and search index entry are renewed.</source>
        <translation>No se escribe nada hasta que apliques el cambio. Entonces primero se crea un punto de restauración de cada versión e idioma, el cambio se ejecuta en una sola transacción que se revierte por completo ante cualquier error, y se renuevan las cachés del objeto y su entrada en el índice de búsqueda.</translation>
    </message>
    <message>
        <source>What the change touches</source>
        <translation>Qué abarca el cambio</translation>
    </message>
    <message>
        <source>versions</source>
        <translation>versiones</translation>
    </message>
    <message>
        <source>languages</source>
        <translation>idiomas</translation>
    </message>
    <message>
        <source>attributes carried over</source>
        <translation>atributos transferidos</translation>
    </message>
    <message>
        <source>old attributes dropped</source>
        <translation>atributos anteriores descartados</translation>
    </message>
    <message>
        <source>drafts</source>
        <translation>borradores</translation>
    </message>
    <message>
        <source>Without --apply the command only shows this preview. Add --apply to change the class; --format=json prints the plan for scripts.</source>
        <translation>Sin --apply el comando solo muestra esta vista previa. Agrega --apply para cambiar la clase; --format=json imprime el plan para scripts.</translation>
    </message>
    <message>
        <source>Changed a select? Update the preview first: the change applies exactly what the preview shows.</source>
        <translation>¿Cambiaste una selección? Primero actualiza la vista previa: el cambio aplica exactamente lo que muestra la vista previa.</translation>
    </message>
    <message>
        <source>Fix what is listed at the top before the class can be changed.</source>
        <translation>Corrige lo que aparece arriba antes de poder cambiar la clase.</translation>
    </message>
    <message>
        <source>Back</source>
        <translation>Regresar</translation>
    </message>
    <message>
        <source>Cancel</source>
        <translation>Cancelar</translation>
    </message>
    <message>
        <source>Change the class</source>
        <translation>Cambiar la clase</translation>
    </message>
    <message>
        <source>Class change of %name</source>
        <translation>Cambio de clase de %name</translation>
    </message>
    <message>
        <source>%name is of the class %class now. Every version and language was changed in one transaction.</source>
        <translation>%name ahora es de la clase %class. Todas las versiones e idiomas se cambiaron en una sola transacción.</translation>
    </message>
    <message>
        <source>The change was rolled back: nothing was changed, the content is exactly as it was.</source>
        <translation>El cambio se revirtió: no se cambió nada, el contenido está exactamente como estaba.</translation>
    </message>
    <message>
        <source>This change was undone: %name is of the class %class again.</source>
        <translation>Este cambio se deshizo: %name vuelve a ser de la clase %class.</translation>
    </message>
    <message>
        <source>The restore point of this change was discarded; the change can no longer be undone from here.</source>
        <translation>El punto de restauración de este cambio se descartó; el cambio ya no se puede deshacer desde aquí.</translation>
    </message>
    <message>
        <source>This change has not finished. If it does not finish within a minute, it was interrupted before it was committed and nothing was changed.</source>
        <translation>Este cambio no ha terminado. Si no termina en un minuto, se interrumpió antes de confirmarse y no se cambió nada.</translation>
    </message>
    <message>
        <source>The change</source>
        <translation>El cambio</translation>
    </message>
    <message>
        <source>Object ID %id</source>
        <translation>ID de objeto %id</translation>
    </message>
    <message>
        <source>From</source>
        <translation>De</translation>
    </message>
    <message>
        <source>Into</source>
        <translation>A</translation>
    </message>
    <message>
        <source>Versions</source>
        <translation>Versiones</translation>
    </message>
    <message>
        <source>Restore point</source>
        <translation>Punto de restauración</translation>
    </message>
    <message>
        <source>%rows attribute rows kept</source>
        <translation>%rows filas de atributos conservadas</translation>
    </message>
    <message>
        <source>Mapping used</source>
        <translation>Asignación utilizada</translation>
    </message>
    <message>
        <source>New attribute</source>
        <translation>Atributo nuevo</translation>
    </message>
    <message>
        <source>Took its content from</source>
        <translation>Tomó su contenido de</translation>
    </message>
    <message>
        <source>(empty)</source>
        <translation>(vacío)</translation>
    </message>
    <message>
        <source>Undo</source>
        <translation>Deshacer</translation>
    </message>
    <message>
        <source>Restoring puts every attribute row of every version and language back as it was before the change, and the class %class with it, in one transaction.</source>
        <translation>Al restaurar, cada fila de atributo de cada versión e idioma vuelve a quedar como antes del cambio, y con ella la clase %class, en una sola transacción.</translation>
    </message>
    <message>
        <source>Restore the object…</source>
        <translation>Restaurar el objeto…</translation>
    </message>
    <message>
        <source>Discard the restore point…</source>
        <translation>Descartar el punto de restauración…</translation>
    </message>
    <message>
        <source>The content of the dropped attributes (and their files) is deleted for good, and the change can no longer be undone.</source>
        <translation>El contenido de los atributos descartados (y sus archivos) se elimina definitivamente, y el cambio ya no se puede deshacer.</translation>
    </message>
    <message>
        <source>Discard</source>
        <translation>Descartar</translation>
    </message>
    <message>
        <source>Next</source>
        <translation>Siguiente</translation>
    </message>
    <message>
        <source>Open %name</source>
        <translation>Abrir %name</translation>
    </message>
    <message>
        <source>Change the other %count objects of %class the same way, in the background</source>
        <translation>Cambiar de la misma forma los otros %count objetos de %class, en segundo plano</translation>
    </message>
    <message>
        <source>All class changes</source>
        <translation>Todos los cambios de clase</translation>
    </message>
    <message>
        <source>From a shell:</source>
        <translation>Desde una terminal:</translation>
    </message>
    <message>
        <source>Change the class of %name</source>
        <translation>Cambiar la clase de %name</translation>
    </message>
    <message>
        <source>The object keeps its id, its locations, its versions and its languages; only its class changes. The next step shows exactly what happens to each attribute before anything is written, and a restore point lets you undo the change.</source>
        <translation>El objeto conserva su ID, sus ubicaciones, sus versiones y sus idiomas; solo cambia su clase. El siguiente paso muestra exactamente qué pasa con cada atributo antes de que se escriba nada, y un punto de restauración te permite deshacer el cambio.</translation>
    </message>
    <message>
        <source>The object now</source>
        <translation>El objeto ahora</translation>
    </message>
    <message>
        <source>Name</source>
        <translation>Nombre</translation>
    </message>
    <message>
        <source>Class</source>
        <translation>Clase</translation>
    </message>
    <message>
        <source>Object ID</source>
        <translation>ID de objeto</translation>
    </message>
    <message>
        <source>Languages</source>
        <translation>Idiomas</translation>
    </message>
    <message>
        <source>Sub-items</source>
        <translation>Subelementos</translation>
    </message>
    <message>
        <source>Objects relating to it</source>
        <translation>Objetos que hacen referencia a él</translation>
    </message>
    <message>
        <source>Modified</source>
        <translation>Modificado</translation>
    </message>
    <message>
        <source>Attributes of %class (%count)</source>
        <translation>Atributos de %class (%count)</translation>
    </message>
    <message>
        <source>The new class</source>
        <translation>La nueva clase</translation>
    </message>
    <message>
        <source>Other</source>
        <translation>Otras</translation>
    </message>
    <message>
        <source>%count classes are left out: your role does not allow changing this object into them.</source>
        <translation>Se omiten %count clases: tu rol no permite cambiar este objeto a ellas.</translation>
    </message>
    <message>
        <source>Your role does not allow changing this object into any class (policy changeclass/convert).</source>
        <translation>Tu rol no permite cambiar este objeto a ninguna clase (política changeclass/convert).</translation>
    </message>
    <message>
        <source>Nothing is changed yet.</source>
        <translation>Todavía no se cambia nada.</translation>
    </message>
    <message>
        <source>Continue to the mapping</source>
        <translation>Continuar a la asignación</translation>
    </message>
    <message>
        <source>Earlier class changes of this object</source>
        <translation>Cambios de clase anteriores de este objeto</translation>
    </message>
    <message>
        <source>Carried over</source>
        <translation>Transferido</translation>
    </message>
    <message>
        <source>Carried over, other datatype</source>
        <translation>Transferido, otro tipo de datos</translation>
    </message>
    <message>
        <source>Converted</source>
        <translation>Convertido</translation>
    </message>
    <message>
        <source>Copied</source>
        <translation>Copiado</translation>
    </message>
    <message>
        <source>Stays empty</source>
        <translation>Queda vacío</translation>
    </message>
    <message>
        <source>Not possible</source>
        <translation>No es posible</translation>
    </message>
    <message>
        <source>Attributes of the new class</source>
        <translation>Atributos de la nueva clase</translation>
    </message>
    <message>
        <source>Choose for each attribute of the new class the attribute of the old class whose content it takes. Only attributes whose content can be carried into it are offered.</source>
        <translation>Elige para cada atributo de la nueva clase el atributo de la clase anterior cuyo contenido toma. Solo se ofrecen los atributos cuyo contenido se puede transferir a él.</translation>
    </message>
    <message>
        <source>Takes its content from</source>
        <translation>Toma su contenido de</translation>
    </message>
    <message>
        <source>How</source>
        <translation>Cómo</translation>
    </message>
    <message>
        <source>Content now</source>
        <translation>Contenido actual</translation>
    </message>
    <message>
        <source>Required</source>
        <translation>Obligatorio</translation>
    </message>
    <message>
        <source>(leave empty)</source>
        <translation>(dejar vacío)</translation>
    </message>
    <message>
        <source>Old attributes nothing takes over</source>
        <translation>Atributos anteriores que nada recoge</translation>
    </message>
    <message>
        <source>Their content is no longer shown after the change. It is not deleted: the restore point keeps it until it is discarded.</source>
        <translation>Su contenido ya no se muestra después del cambio. No se elimina: el punto de restauración lo conserva hasta que se descarte.</translation>
    </message>
    <message>
        <source>Old attribute</source>
        <translation>Atributo anterior</translation>
    </message>
    <message>
        <source>Datatype</source>
        <translation>Tipo de datos</translation>
    </message>
    <message>
        <source>(empty in every language)</source>
        <translation>(vacío en todos los idiomas)</translation>
    </message>
    <message>
        <source>Every old attribute is taken over: nothing is dropped.</source>
        <translation>Se recogen todos los atributos anteriores: no se descarta nada.</translation>
    </message>
    <message>
        <source>The class cannot be changed yet:</source>
        <translation>Todavía no se puede cambiar la clase:</translation>
    </message>
    <message>
        <source>Before you apply the change:</source>
        <translation>Antes de aplicar el cambio:</translation>
    </message>
    <message>
        <source>Running</source>
        <translation>En ejecución</translation>
    </message>
    <message>
        <source>Steps</source>
        <translation>Pasos</translation>
    </message>
    <message>
        <source>Choose the new class</source>
        <translation>Elegir la nueva clase</translation>
    </message>
    <message>
        <source>Map the attributes and preview</source>
        <translation>Asignar los atributos y vista previa</translation>
    </message>
    <message>
        <source>Result and restore point</source>
        <translation>Resultado y punto de restauración</translation>
    </message>
    <message>
        <source>The object is of the class %class already.</source>
        <translation>El objeto ya es de la clase %class.</translation>
    </message>
    <message>
        <source>The object is in the trash. Restore it before changing its class.</source>
        <translation>El objeto está en la papelera. Restáuralo antes de cambiar su clase.</translation>
    </message>
    <message>
        <source>The object is a user account. The class of user accounts cannot be changed here.</source>
        <translation>El objeto es una cuenta de usuario. La clase de las cuentas de usuario no se puede cambiar aquí.</translation>
    </message>
    <message>
        <source>The object is locked. Unlock it before changing its class.</source>
        <translation>El objeto está bloqueado. Desbloquéalo antes de cambiar su clase.</translation>
    </message>
    <message>
        <source>You may not edit this object, so you may not change its class either.</source>
        <translation>No puedes editar este objeto, así que tampoco puedes cambiar su clase.</translation>
    </message>
    <message>
        <source>A background job works on this part of the tree (%job). Wait until it is done.</source>
        <translation>Un trabajo en segundo plano está procesando esta parte del árbol (%job). Espera a que termine.</translation>
    </message>
    <message>
        <source>The object has no versions or no languages to change.</source>
        <translation>El objeto no tiene versiones ni idiomas que cambiar.</translation>
    </message>
    <message>
        <source>The class %class has no attributes.</source>
        <translation>La clase %class no tiene atributos.</translation>
    </message>
    <message>
        <source>The class %class has an attribute of the datatype %datatype (%attribute), which cannot be changed into.</source>
        <translation>La clase %class tiene un atributo del tipo de datos %datatype (%attribute), al que no se puede cambiar.</translation>
    </message>
    <message>
        <source>The attribute %attribute is of the datatype %datatype, which cannot be changed from.</source>
        <translation>El atributo %attribute es del tipo de datos %datatype, desde el que no se puede cambiar.</translation>
    </message>
    <message>
        <source>The class %class has no attribute %identifier.</source>
        <translation>La clase %class no tiene el atributo %identifier.</translation>
    </message>
    <message>
        <source>%source (%source_datatype) cannot be carried into %destination (%destination_datatype).</source>
        <translation>%source (%source_datatype) no se puede transferir a %destination (%destination_datatype).</translation>
    </message>
    <message>
        <source>%source is carried into %destination as well: a copy of the current version&apos;s content.</source>
        <translation>%source también se transfiere a %destination: una copia del contenido de la versión actual.</translation>
    </message>
    <message>
        <source>%destination is required and stays empty: the next edit asks for a value before it can be published.</source>
        <translation>%destination es obligatorio y queda vacío: la siguiente edición pedirá un valor antes de poder publicarse.</translation>
    </message>
    <message>
        <source>%source holds content in %languages that the new class does not take over. It is kept in the restore point until that is discarded.</source>
        <translation>%source tiene contenido en %languages que la nueva clase no recoge. Se conserva en el punto de restauración hasta que este se descarte.</translation>
    </message>
    <message>
        <source>The object has %count sub-items, but %class is no container: they stay, but no new ones can be added below it.</source>
        <translation>El objeto tiene %count subelementos, pero %class no es un contenedor: se mantienen, pero no se pueden agregar nuevos bajo él.</translation>
    </message>
    <message>
        <source>%count drafts of other users are changed too. Tell them before they publish an edit that was open during the change.</source>
        <translation>También se cambian %count borradores de otros usuarios. Avísales antes de que publiquen una edición que estaba abierta durante el cambio.</translation>
    </message>
    <message>
        <source>%count drafts are changed too.</source>
        <translation>También se cambian %count borradores.</translation>
    </message>
    <message>
        <source>%count objects relate to this one through an attribute. A relation attribute that only accepts certain classes may no longer accept it.</source>
        <translation>%count objetos hacen referencia a este mediante un atributo. Un atributo de relación que solo acepta ciertas clases puede dejar de aceptarlo.</translation>
    </message>
    <message>
        <source>The object no longer exists.</source>
        <translation>El objeto ya no existe.</translation>
    </message>
    <message>
        <source>The object is no longer of the class it was changed into.</source>
        <translation>El objeto ya no es de la clase a la que se cambió.</translation>
    </message>
    <message>
        <source>The object was edited after the change: restoring would lose that work. Change its class back instead.</source>
        <translation>El objeto se editó después del cambio: restaurarlo haría perder ese trabajo. En su lugar, vuelve a cambiar su clase.</translation>
    </message>
    <message>
        <source>The old class no longer exists.</source>
        <translation>La clase anterior ya no existe.</translation>
    </message>
    <message>
        <source>The old class lost an attribute since the change.</source>
        <translation>La clase anterior perdió un atributo desde el cambio.</translation>
    </message>
    <message>
        <source>Your role does not allow changing %class into %new_class here (policy changeclass/convert).</source>
        <translation>Tu rol no permite cambiar %class a %new_class aquí (política changeclass/convert).</translation>
    </message>
    <message>
        <source>The object or the mapping changed since the preview. Check the new preview and apply again.</source>
        <translation>El objeto o la asignación cambiaron desde la vista previa. Revisa la nueva vista previa y vuelve a aplicar.</translation>
    </message>
    <message>
        <source>The restore point could not be written to %dir, so nothing was changed.</source>
        <translation>No se pudo escribir el punto de restauración en %dir, así que no se cambió nada.</translation>
    </message>
    <message>
        <source>The change was rolled back, nothing was changed: %reason</source>
        <translation>El cambio se revirtió, no se cambió nada: %reason</translation>
    </message>
    <message>
        <source>The class was changed.</source>
        <translation>Se cambió la clase.</translation>
    </message>
    <message>
        <source>The class was not changed. The content is exactly as it was.</source>
        <translation>No se cambió la clase. El contenido está exactamente como estaba.</translation>
    </message>
    <message>
        <source>Your role does not allow changing %class into %new_class here (policy changeclass/bulk).</source>
        <translation>Tu rol no permite cambiar %class a %new_class aquí (política changeclass/bulk).</translation>
    </message>
    <message>
        <source>There is no published object of the class %class below this node.</source>
        <translation>No hay ningún objeto publicado de la clase %class bajo este nodo.</translation>
    </message>
    <message>
        <source>Nothing was started: check the new preview.</source>
        <translation>No se inició nada: revisa la nueva vista previa.</translation>
    </message>
    <message>
        <source>The job was not started.</source>
        <translation>El trabajo no se inició.</translation>
    </message>
    <message>
        <source>There is no role %role.</source>
        <translation>No existe el rol %role.</translation>
    </message>
    <message>
        <source>The role %role may change classes now: %policies.</source>
        <translation>El rol %role ahora puede cambiar clases: %policies.</translation>
    </message>
    <message>
        <source>The role %role held these policies already; nothing was added.</source>
        <translation>El rol %role ya tenía estas políticas; no se agregó nada.</translation>
    </message>
    <message>
        <source>The role %role may no longer change classes: %policies removed.</source>
        <translation>El rol %role ya no puede cambiar clases: %policies eliminadas.</translation>
    </message>
    <message>
        <source>The role %role had no class change policies; nothing was removed.</source>
        <translation>El rol %role no tenía políticas de cambio de clase; no se eliminó nada.</translation>
    </message>
    <message>
        <source>Preview the change before applying it.</source>
        <translation>Revisa la vista previa del cambio antes de aplicarlo.</translation>
    </message>
    <message>
        <source>Nothing was changed: check the new preview.</source>
        <translation>No se cambió nada: revisa la nueva vista previa.</translation>
    </message>
    <message>
        <source>Map the attributes</source>
        <translation>Asignar los atributos</translation>
    </message>
    <message>
        <source>Result</source>
        <translation>Resultado</translation>
    </message>
    <message>
        <source>Your role does not allow undoing this change (policy changeclass/restore).</source>
        <translation>Tu rol no permite deshacer este cambio (política changeclass/restore).</translation>
    </message>
    <message>
        <source>The change was not undone.</source>
        <translation>El cambio no se deshizo.</translation>
    </message>
    <message>
        <source>The object is of the class %class again, with all its attributes as they were.</source>
        <translation>El objeto vuelve a ser de la clase %class, con todos sus atributos tal como estaban.</translation>
    </message>
    <message>
        <source>This restore point cannot be discarded.</source>
        <translation>Este punto de restauración no se puede descartar.</translation>
    </message>
    <message>
        <source>The restore point was discarded: the dropped data is gone for good.</source>
        <translation>El punto de restauración se descartó: los datos descartados se perdieron definitivamente.</translation>
    </message>
    <message>
        <source>Your role does not allow changing this object into that class (policy changeclass/convert).</source>
        <translation>Tu rol no permite cambiar este objeto a esa clase (política changeclass/convert).</translation>
    </message>
    <message>
        <source>This change is pending, there is nothing to restore.</source>
        <translation>Este cambio está pendiente, no hay nada que restaurar.</translation>
    </message>
    <message>
        <source>This change is restored, there is nothing to restore.</source>
        <translation>Este cambio ya está restaurado, no hay nada que restaurar.</translation>
    </message>
    <message>
        <source>This change is discarded, there is nothing to restore.</source>
        <translation>Este cambio está descartado, no hay nada que restaurar.</translation>
    </message>
    <message>
        <source>This change is failed, there is nothing to restore.</source>
        <translation>Este cambio falló, no hay nada que restaurar.</translation>
    </message>
</context>
</TS>
