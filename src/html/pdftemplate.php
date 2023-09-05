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
        }

        /* Header */
        div.header--container {
            width: 100%;
            text-align: center;
            display: flex;
        }

        div.header--container img {
            margin: auto;
            max-height: 8rem;
        }

        /* Introduction */
        div.introduction--container {
            font-family: 'Times New Roman', Times, serif;
            text-align: center;
            line-height: 1.2rem;
            margin-bottom: 2rem;
        }

        div.introduction--container p {
            margin-bottom: 2rem;
        }

        div.introduction--container strong {
            font-size: 16px;
        }

        /* Content */
        div.content--container {
            font-family: 'Times New Roman', Times, serif;
            text-align: justify;
            line-height: 1.5rem;
        }

        div.content--container p {
            margin: 0px;
            font-size: 16px;
            margin-bottom: 0.5rem;
        }

        div.content--container p:nth-of-type(2) {
            margin-bottom: 0.75rem;
        }

        /* Sender */
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
            border-bottom: solid 2px #000000;
            margin: auto;
        }

        div.sender--container div img {
            position: relative;
            height: 100%;
            max-height: 100%;
        }

        div.sender--container span {
            display: flex;
            flex-direction: row;
        }

        div.sender--container span:nth-of-type(1) {
            text-transform: uppercase;
        }

        /* About journal */
        div.about-journal--container {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            text-align: justify;
            line-height: 1.2rem;
            margin-bottom: 0.4rem;
        }

        /* Journal info */
        div.journal-info--container {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            text-align: justify;
            line-height: 1.2rem;
            margin-bottom: 2rem;
        }
        div.journal-info--container p{
            font-weight: bold;
        }

        /* Footer */
        div.footer--container {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 0.7rem;
            text-align: center;
        }

        div.footer--container span {
            display: flex;
            flex-direction: row;
        }
    </style>
</head>

<body>
    <!-- header container -->
    <div class="header--container">
        <img src="${journal_editorial_header_image}" alt="${journal_name}" />
    </div>
    <!-- introduction container -->
    <div class="introduction--container">
        <p>
            El Equipo Editorial de la <br />
            Revista <i>${journal_name}</i>, <br />
            ${journal_educational_program_name} <br />
            ${journal_institution_name}
        </p>
        <strong>Certifica que:</strong>
    </div>
    <!-- content container -->
    <div class="content--container">
        <p>El/La profesor/a <strong>${reviewer_first_name} ${reviewer_last_name}</strong>, de la <strong>${reviewer_affiliation}</strong>, ha sido par evaluador/a de este órgano de difusión y colaboró con el proceso de evaluación del artículo <strong><i>${publication_title}</i></strong>.</p>
        <p>La presente certificación se expide en Tunja el día ${publication_day} del mes de ${publication_month} de ${publication_year}.</p>
        <p>Cordialmente,</p>
    </div>
    <!-- sender container -->
    <div class="sender--container">
        <div>
            <img src="${journal_editor_signature}" alt="${journal_editor_name} signature" />
        </div>
        <span>${journal_editor_name}</span>
        <span>Revista <i>${journal_name}</i>,</span>
        <span>${journal_institution_name}<span>
    </div>
    <!-- about journal -->
    <div class="about-journal--container">
        <p>${journal_description} <strong>ISSN: ${journal_issn} / EISSN: ${journal_eissn}.</strong></p>
    </div>
    <!-- journai info -->
    <div class="journal-info--container">
        <p>${journal_certificate_journal_info}</p>
    </div>
    <!-- footer -->
    <div class="footer--container">
        <span>${journal_institution_name}</span>
        <span>${journal_academic_unit_name_1}</span>
        <span>${journal_academic_unit_name_2} ${journal_educational_program_name}</span>
        <span>${journal_editorial_address}</span>
        <span>${journal_editorial_phone}</span>
        <span><a href="${journal_url}">${journal_url}</a>, <a href="mailto:${journal_url}">${journal_email}</a></span>
    </div>
</body>

</html>