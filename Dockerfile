# Copyright 2016  Martin Scharm
#
# This file is part of TEXPILE.
# <https://github.com/binfalse/TEXPILE>
#
# TEXPILE is free software: you can redistribute it and/or modify it
# under the terms of the GNU General Public License as published by the Free
# Software Foundation, either version 3 of the License, or (at your option) any
# later version.
#
# TEXPILE is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

# You should have received a copy of the GNU General Public License along with
# TEXPILE. If not, see <http://www.gnu.org/licenses/>.



# let's use an apache+php image as a base
FROM php:apache

# see http://binfalse.de/contact/ if you want to contact me
MAINTAINER martin scharm

# install texlive
RUN apt-get -y update && apt-get install -y texlive-full python-pygments

# copy the 
COPY index.php /var/www/html/

