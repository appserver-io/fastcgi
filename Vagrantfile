Vagrant.configure("2") do |config|
  config.vm.box = "debian/wheezy64"
  config.vm.network "private_network", type: "dhcp"

  config.vm.provision "shell",
    name: "APT",
    keep_color: true,
    inline: "/usr/bin/apt-get update -y && /usr/bin/apt-get install -y php5-cli php5-fpm"
  config.vm.provision "shell",
    name: "Composer",
    keep_color: true,
    inline: "/usr/bin/wget -nv -O/usr/local/bin/composer https://getcomposer.org/composer.phar; /bin/chmod +x /usr/local/bin/composer"
  # PhpUnit 4.5.x because thats the version travis uses
  config.vm.provision "shell",
    name: "PhpUnit",
    keep_color: true,
    inline: "/usr/bin/wget -nv -O/usr/local/bin/phpunit https://phar.phpunit.de/phpunit-4.5.1.phar; /bin/chmod +x /usr/local/bin/phpunit"
  config.vm.provision "shell",
    name: "FPM-Setup",
    keep_color: true,
    inline: "sed -i 's/^;*\s*listen\.mode.*$/listen.mode = 0777/' /etc/php5/fpm/pool.d/www.conf && service php5-fpm restart"
end
