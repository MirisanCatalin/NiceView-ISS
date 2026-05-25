const judete = {
    "CJ": "Cluj",
    "BV": "Brasov",
    "CT": "Constanta",
    "HR": "Harghita",
    "SJ": "Salaj",
    "MM": "Maramures",
    "AB": "Alba",
    "SB": "Sibiu"
};

const localitati = {
    "CJ": ["Cluj-Napoca", "Turda", "Dej", "Campia Turzii", "Gherla"],
    "BV": ["Brasov", "Fagaras", "Sacele", "Zarnesti", "Predeal","Rasnov"],
    "CT": ["Constanta", "Mangalia", "Navodari", "Eforie Nord", "Techirghiol"],
    "HR": ["Miercurea Ciuc", "Odorheiu Secuiesc", "Gheorgheni", "Toplita", "Baile Tusnad"],
    "SJ": ["Zalau", "Simleu Silvaniei", "Jibou", "Cehu Silvaniei", "Nusfalau"],
    "MM": ["Baia Mare", "Sighetu Marmatiei", "Borsa", "Viseu de Sus", "Targu Lapus"],
    "AB": ["Alba Iulia", "Sebes", "Blaj", "Aiud", "Campeni"],
    "SB": ["Sibiu", "Medias", "Cisnadie", "Avrig", "Agnita"]
};

const privelisti = [
    {
        titlu: "Apus in Apuseni",
        judet: "CJ",
        localitate: "Cluj-Napoca",
        tip: "munte",
        altitudine: 1200,
        sezon: "Vara",
        dificultate: "mediu",
        descriere: "Priveliște spectaculoasă asupra munților.",
        lat: 46.77,
        lng: 23.59,
        imagine: "../images/munte.jpg"
    },
    {
        titlu: "Lacul Rosu",
        judet: "HR",
        localitate: "Gheorgheni",
        tip: "lac",
        altitudine: 983,
        sezon: "Vara",
        dificultate: "usor",
        descriere: "Lac de baraj natural format in urma unui cutremur.",
        lat: 46.77,
        lng: 25.75,
        imagine: "../images/munte.jpg"
    },
    {
        titlu: "Marea Neagra",
        judet: "CT",
        localitate: "Constanta",
        tip: "mare",
        altitudine: 0,
        sezon: "Vara",
        dificultate: "usor",
        descriere: "Litoral romanesc cu plaje intinse.",
        lat: 44.17,
        lng: 28.63,
        imagine: "../images/mareneagra.jpg"
    },
    {
        titlu: "Varful Moldoveanu",
        judet: "AB",
        localitate: "Campeni",
        tip: "munte",
        altitudine: 2544,
        sezon: "Vara",
        dificultate: "dificil",
        descriere: "Cel mai inalt varf din Romania.",
        lat: 45.60,
        lng: 24.73,
        imagine: "../images/vfmoldoveanu.jpg"
    },
    {
        titlu: "Transfagarasan",
        judet: "SB",
        localitate: "Sibiu",
        tip: "munte",
        altitudine: 2042,
        sezon: "Vara",
        dificultate: "mediu",
        descriere: "Unul dintre cele mai spectaculoase drumuri din lume.",
        lat: 45.60,
        lng: 24.61,
        imagine: "../images/trfg.jpg"
    },
    {
        titlu: "Cascada Cailor",
        judet: "MM",
        localitate: "Borsa",
        tip: "munte",
        altitudine: 1300,
        sezon: "Primavara",
        dificultate: "usor",
        descriere: "Cea mai inalta cascada din Romania.",
        lat: 47.65,
        lng: 24.84,
        imagine: "../images/cascai.jpg"
    }
];

const altitudineMaxima = {
    "munte": 3000,
    "mare": 10,
    "lac": 2000,
    "oras": 500
};

const carousel = [
    {
        link: "index-horizontal.html",
        text: "Apus in Apuseni - Cluj",
        imagine: "../images/munte.jpg"
    },
    {
        link: "index-horizontal.html",
        text: "Lacul Rosu - Harghita",
        imagine: "../images/lacrosu.jpg"
    },
    {
        link: "index-horizontal.html",
        text: "Marea Neagra - Constanta",
        imagine: "../images/mareneagra.jpg"
    },
    {
        link: "index-horizontal.html",
        text: "Varful Moldoveanu - Alba",
        imagine: "../images/vfmoldoveanu.jpg"
    }
];

