<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado</title>
    <style>
        /* General */
        @page {
            margin: 2cm 2.5cm !important;
            position: relative;
        }

        div.background-watermark--certificate {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            max-width: 16cm;
            width: 16cm;
            min-width: 16cm;
        }
        div.background-watermark--certificate img {
            width: 100%;
            opacity: 0.2;
        }

        /* Header */
        div.header--container {
            width: 100%;
            text-align: center;
            display: flex;
            margin: -1cm auto;
            padding-bottom: 50px;
            z-index: 2;
        }

        div.header--container img {
            margin: auto;
            max-width: 100%;
            width: 100%;
            min-width: 100%;
            opacity: 0.5;

        }

        div.certificate--content {
            z-index: 2;
        }

        div.certificate--content p {
            font-family: 'Times New Roman', Times, serif;
            font-style: 12px;
            font-style: italic;
            text-align: justify;
        }

        div.certificate--content p:first-of-type {
            font-size: 20px !important;
            font-style: normal !important;
            text-align: center !important;
            font-weight: lighter !important;
        }

        div.sender--container {
            font-family: 'Times New Roman', Times, serif;
            font-size: 16px;
            text-align: center;
            line-height: 1.2rem;
            margin-bottom: 2rem;
        }

        div.sender--container div {
            position: relative;
            height: 3cm;
            max-height: 3cm;
            width: 8cm;
            max-width: 8cm;
            /* border-bottom: solid 2px #000000; */
            margin: auto;
        }

        div.sender--container div img {
            position: relative;
            height: 100%;
            max-height: 100%;
            opacity: 0.9;
        }

        div.sender--container span {
            display: flex;
            flex-direction: row;
        }

        div.sender--container span:nth-of-type(1) {
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="background-watermark--certificate">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Logo_de_la_UPTC.svg/1200px-Logo_de_la_UPTC.svg.png" alt="">
    </div>
    <!-- header container -->
    <div class="header--container">
        <img src="${journal_editorial_header_image}" alt="${journal_name}" />
    </div>
    <div class="certificate--content">
        <p>
            A quien le interese:
        </p>
        <br>
        <p>
            Se certifica que el Dr /a <strong>${reviewer_first_name} ${reviewer_last_name}</strong>, fue evaluador del manuscrito titulado “<strong><i>${publication_title}</i></strong>” donde se hizo una revisión del cumplimiento de los parámetros del manuscrito, además se verifico la información contenida en el manuscrito y su originalidad.
        </p>
        <br>
        <p>
            La Revista Ciencia en Desarrollo es una revista editada por el Centro de Investigaciones y Extensión de la Facultad de Ciencias (CIEC) de la Universidad Pedagógica y Tecnológica de Colombia UPTC, que busca contribuir con la difusión de los nuevos conocimientos científicos y desarrollos tecnológicos que se producen en el campo de las ciencias básicas (biología, física, matemáticas y química) y es un espacio abierto que permite a los investigadores e interesados en el tema un acceso directo a los contenidos de una manera libre y gratuita para todo el público, de tal manera que permitirá una intersección constante de sus publicaciones a nivel mundial en el conocimiento científico, publicando en idioma español, inglés o portugués y actualmente está indexada en Publindex B (Colciencias-Colombia).
        </p>
        <br>
        <p>
            La expedición del presente certificado se hace a solicitud del interesado a los <strong>${publication_day}</strong> días del mes de <strong>${publication_month}</strong> de <strong>${publication_year}</strong>.
        </p>
        <br>
        <p>
            Cordialmente,
        </p>

    </div>
    <div class="sender--container">
        <div>
            <img src="${journal_editor_signature}" alt="${journal_editor_name} signature" />
        </div>
        <span>${journal_editor_name}</span>
        <span>Revista <i>${journal_name}</i>,</span>
        <span>${journal_institution_name}<span>
    </div>
    <!-- introduction container -->
    <!-- <div class="introduction--container">
        <p>
            El Equipo Editorial de la <br />
            Revista <i>${journal_name}</i>, <br />
            ${journal_educational_program_name} <br />
            ${journal_institution_name}
        </p>
        <strong>Certifica que:</strong>
    </div> -->
    <!-- content container -->
    <!-- <div class="content--container">
        <p>El/La profesor/a <strong>${reviewer_first_name} ${reviewer_last_name}</strong>, de la <strong>${reviewer_affiliation}</strong>, ha sido par evaluador/a de este órgano de difusión y colaboró con el proceso de evaluación del artículo <strong><i>${publication_title}</i></strong>.</p>
        <p>La presente certificación se expide en Tunja el día ${publication_day} del mes de ${publication_month} de ${publication_year}.</p>
        <p>Cordialmente,</p>
    </div> -->
    <!-- sender container -->
    <!-- <div class="sender--container">
        <div>
            <img src="${journal_editor_signature}" alt="${journal_editor_name} signature" />
        </div>
        <span>${journal_editor_name}</span>
        <span>Revista <i>${journal_name}</i>,</span>
        <span>${journal_institution_name}<span>
    </div> -->
    <!-- about journal -->
    <!-- <div class="about-journal--container">
        <p>${journal_description} <strong>ISSN: ${journal_issn} / EISSN: ${journal_eissn}.</strong></p>
    </div> -->
    <!-- journai info -->
    <!-- <div class="journal-info--container">
        <p>${journal_certificate_journal_info}</p>
    </div> -->
    <!-- footer -->
    <!-- <div class="footer--container">
        <span>${journal_institution_name}</span>
        <span>${journal_academic_unit_name_1}</span>
        <span>${journal_academic_unit_name_2} ${journal_educational_program_name}</span>
        <span>${journal_editorial_address}</span>
        <span>${journal_editorial_phone}</span>
        <span><a href="${journal_url}">${journal_url}</a>, <a href="mailto:${journal_url}">${journal_email}</a></span>
    </div> -->
</body>

</html>