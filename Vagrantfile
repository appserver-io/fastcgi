Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/trusty64"

  config.vm.network "private_network", type: "dhcp"

  if Vagrant.has_plugin?("landrush")
    config.landrush.tld = config.vm.hostname
  end

  # get rid of ubuntus annoying "Not a tty"-message
  config.ssh.shell = "bash -c 'BASH_ENV=/etc/profile exec bash'"

  config.vm.provision "shell", inline: "/usr/bin/wget -nv -O/usr/local/bin/composer https://getcomposer.org/composer.phar; /bin/chmod +x /usr/local/bin/composer"
  config.vm.provision "shell", inline: "/usr/bin/apt-get install -y php5-cli php5-fpm"
end
