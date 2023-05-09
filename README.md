# JrBka_P6_22-03-2023
** Notice d'utilisation du projet avec lancement sous XAMPP **

Pour utiliser ce projet, veuillez cloner ce référentiel. Rendez-vous dans le terminal de votre éditeur de code et saisissez :
git clone https://github.com/JrBka/JrBka_P6_22-03-2023.git

Quelques modifications sont nécessaires avant que le projet soit fonctionnel :

- installez composeur à la racine du projet
- ouvrez le fichier .env et remplacez toutes les valeurs par les valeurs correspondantes à votre installation.

Si vous utilisez un mailer local comme 'mailhog', lancez le.

Lancez XAMPP.

Une fois le projet installé et configuré ouvrez votre gestionnaire de base de données et importez le fichier snowtricks.sql.

Rendez vous à nouveau dans votre terminal et saisissez : 'symfony serve'.
Ouvrez un second terminal et saisissez : 'symfony console d:f:l' pour charger les données initiales.

Le projet est en place, rendez vous dans votre navigateur à l'adresse http://localhost:8000 pour l'utiliser.

