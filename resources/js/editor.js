import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';
import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic';
import Underline from '@ckeditor/ckeditor5-basic-styles/src/underline';
import Heading from '@ckeditor/ckeditor5-heading/src/heading';
import PasteFromOffice from '@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice';
import Undo from '@ckeditor/ckeditor5-undo/src/undo';
import List from '@ckeditor/ckeditor5-list/src/list';
import Link from '@ckeditor/ckeditor5-link/src/link';

ClassicEditor
    .create( document.querySelector( '#editor' ), {
        plugins: [ Essentials, Paragraph, Bold, Italic, Underline, Heading, List, Link, Undo, PasteFromOffice ],
        toolbar: [ 'heading', '|', 'bold', 'italic', 'underline', 'link', 'numberedlist', 'bulletedlist', '|', 'undo', 'redo' ],
        link: {
            addTargetToExternalLinks: true
        }
    })
    .catch( error => {
        console.error( error.stack );
    });
