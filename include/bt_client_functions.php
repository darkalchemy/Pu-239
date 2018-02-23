<?php
/**
 * @param $id_data
 * @param $id_name
 *
 * @return string
 */
function StdDecodePeerId($id_data, $id_name)
{
    $version_str = '';
    for ($i = 0; $i <= strlen($id_data); ++$i) {
        $c = $id_data[$i];
        if ('BitTornado' == $id_name || 'ABC' == $id_name) {
            if ('-' != $c && ctype_digit($c)) {
                $version_str .= "$c.";
            } elseif ('-' != $c && ctype_alpha($c)) {
                $version_str .= (ord($c) - 55) . '.';
            } else {
                break;
            }
        } elseif ('BitComet' == $id_name || 'BitBuddy' == $id_name || 'Lphant' == $id_name || 'BitPump' == $id_name || 'BitTorrent Plus! v2' == $id_name) {
            if ('-' != $c && ctype_alnum($c)) {
                $version_str .= "$c";
                if (0 == $i) {
                    $version_str = intval($version_str) . '.';
                }
            } else {
                $version_str .= '.';
                break;
            }
        } else {
            if ('-' != $c && ctype_alnum($c)) {
                $version_str .= "$c.";
            } else {
                break;
            }
        }
    }
    $version_str = substr($version_str, 0, strlen($version_str) - 1);

    return "$id_name $version_str";
}

/**
 * @param $id_data
 * @param $id_name
 *
 * @return string
 */
function MainlineDecodePeerId($id_data, $id_name)
{
    $version_str = '';
    for ($i = 0; $i <= strlen($id_data); ++$i) {
        $c = isset($id_data[$i]) ? $id_data[$i] : '-';
        if ('-' != $c && ctype_alnum($c)) {
            $version_str .= "$c.";
        }
    }
    $version_str = substr($version_str, 0, strlen($version_str) - 1);

    return "$id_name $version_str";
}

/**
 * @param $ver_data
 * @param $id_name
 *
 * @return string
 */
function DecodeVersionString($ver_data, $id_name)
{
    $version_str = '';
    $version_str .= intval(ord($ver_data[0]) + 0) . '.';
    $version_str .= intval(ord($ver_data[1]) / 10 + 0);
    $version_str .= intval(ord($ver_data[1]) % 10 + 0);

    return "$id_name $version_str";
}

/**
 * @param        $httpagent
 * @param string $peer_id
 *
 * @return string
 */
function getagent($httpagent, $peer_id = '')
{
    // if($peer_id!="") $peer_id=hex2bin($peer_id);
    if ('-AX' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 4, 4), 'BitPump');
    } // AnalogX BitPump
    if ('-BB' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 5), 'BitBuddy');
    } // BitBuddy
    if ('-BC' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 4, 4), 'BitComet');
    } // BitComet
    if ('-BS' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'BTSlave');
    } // BTSlave
    if ('-BX' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'BittorrentX');
    } // BittorrentX
    if ('-CT' == substr($peer_id, 0, 3)) {
        return "Ctorrent $peer_id[3].$peer_id[4].$peer_id[6]";
    } // CTorrent
    if ('-KT' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'KTorrent');
    } // KTorrent
    if ('-LT' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'libtorrent');
    } // libtorrent
    if ('-LP' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 4, 4), 'Lphant');
    } // Lphant
    if ('-MP' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'MooPolice');
    } // MooPolice
    if ('-MT' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'Moonlight');
    } // MoonlightTorrent
    if ('-PO' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'PO Client');
    } //unidentified clients with versions
    if ('-QT' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'Qt 4 Torrent');
    } // Qt 4 Torrent
    if ('-RT' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'Retriever');
    } // Retriever
    if ('-S2' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'S2 Client');
    } //unidentified clients with versions
    if ('-SB' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'Swiftbit');
    } // Swiftbit
    if ('-SN' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'ShareNet');
    } // ShareNet
    if ('-SS' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'SwarmScope');
    } // SwarmScope
    if ('-SZ' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'Shareaza');
    } // Shareaza
    if (preg_match("/^RAZA ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches)) {
        return "Shareaza $matches[1]";
    }
    if ('-TN' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'Torrent.NET');
    } // Torrent.NET
    if ('-TR' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'Transmission');
    } // Transmission
    if ('-TS' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'TorrentStorm');
    } // Torrentstorm
    if ('-UR' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'UR Client');
    } // unidentified clients with versions
    if ('-UT' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'uTorrent');
    } // uTorrent
    if ('-XT' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'XanTorrent');
    } // XanTorrent
    if ('-ZT' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'ZipTorrent');
    } // ZipTorrent
    if ('-bk' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'BitKitten');
    } // BitKitten
    if ('-lt' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'libTorrent');
    } // libTorrent
    if ('-pX' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'pHoeniX');
    } // pHoeniX
    if ('BG' == substr($peer_id, 0, 2)) {
        return StdDecodePeerId(substr($peer_id, 2, 4), 'BTGetit');
    } // BTGetit
    if ('BM' == substr($peer_id, 2, 2)) {
        return DecodeVersionString(substr($peer_id, 0, 2), 'BitMagnet');
    } // BitMagnet
    if ('OP' == substr($peer_id, 0, 2)) {
        return StdDecodePeerId(substr($peer_id, 2, 4), 'Opera');
    } // Opera
    if ('-qB' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 7), 'qBittorrent');
    } // libTorrent
    if ('270-' == substr($peer_id, 0, 4)) {
        return 'GreedBT 2.7.0';
    } // GreedBT
    if ('271-' == substr($peer_id, 0, 4)) {
        return 'GreedBT 2.7.1';
    } // GreedBT 2.7.1
    if ('346-' == substr($peer_id, 0, 4)) {
        return 'TorrentTopia';
    } // TorrentTopia
    if ('-AR' == substr($peer_id, 0, 3)) {
        return 'Arctic Torrent';
    } // Arctic (no way to know the version)
    if ('-G3' == substr($peer_id, 0, 3)) {
        return 'G3 Torrent';
    } // G3 Torrent
    if ('BTDWV-' == substr($peer_id, 0, 6)) {
        return 'Deadman Walking';
    } // Deadman Walking
    if ('Azureus' == substr($peer_id, 5, 7)) {
        return 'Azureus 2.0.3.2';
    } // Azureus 2.0.3.2
    if ('PRC.P---' == substr($peer_id, 0, 8)) {
        return 'BitTorrent Plus! II';
    } // BitTorrent Plus! II
    if ('S587Plus' == substr($peer_id, 0, 8)) {
        return 'BitTorrent Plus!';
    } // BitTorrent Plus!
    if ('martini' == substr($peer_id, 0, 7)) {
        return 'Martini Man';
    } // Martini Man
    if ('btfans' == substr($peer_id, 4, 6)) {
        return 'SimpleBT';
    } // SimpleBT
    if ('SimpleBT?' == substr($peer_id, 3, 9)) {
        return 'SimpleBT';
    } // SimpleBT
    if (preg_match('/MFC_Tear_Sample/', preg_quote($httpagent))) {
        return 'SimpleBT';
    }
    if ('btuga' == substr($peer_id, 0, 5)) {
        return 'BTugaXP';
    } // BTugaXP
    if ('BTuga' == substr($peer_id, 0, 5)) {
        return 'BTuga';
    } // BTugaXP
    if ('oernu' == substr($peer_id, 0, 5)) {
        return 'BTugaXP';
    } // BTugaXP
    if ('DansClient' == substr($peer_id, 0, 10)) {
        return 'XanTorrent';
    } // XanTorrent
    if ('Deadman Walking-' == substr($peer_id, 0, 16)) {
        return 'Deadman';
    } // Deadman client
    if ('XTORR302' == substr($peer_id, 0, 8)) {
        return 'TorrenTres 0.0.2';
    } // TorrenTres
    if ('turbobt' == substr($peer_id, 0, 7)) {
        return 'TurboBT ' . (substr($peer_id, 7, 5));
    } // TurboBT
    if ('a00---0' == substr($peer_id, 0, 7)) {
        return 'Swarmy';
    } // Swarmy
    if ('a02---0' == substr($peer_id, 0, 7)) {
        return 'Swarmy';
    } // Swarmy
    if ('T00---0' == substr($peer_id, 0, 7)) {
        return 'Teeweety';
    } // Teeweety
    if ('rubytor' == substr($peer_id, 0, 7)) {
        return 'Ruby Torrent v' . ord($peer_id[7]);
    } // Ruby Torrent
    if ('Mbrst' == substr($peer_id, 0, 5)) {
        return MainlineDecodePeerId(substr($peer_id, 5, 5), 'burst!');
    } // burst!
    if ('btpd' == substr($peer_id, 0, 4)) {
        return 'BT Protocol Daemon ' . (substr($peer_id, 5, 3));
    } // BT Protocol Daemon
    if ('XBT022--' == substr($peer_id, 0, 8)) {
        return 'BitTorrent Lite';
    } // BitTorrent Lite based on XBT code
    if ('XBT' == substr($peer_id, 0, 3)) {
        return StdDecodePeerId(substr($peer_id, 3, 3), 'XBT');
    } // XBT Client
    if ('-BOW' == substr($peer_id, 0, 4)) {
        return StdDecodePeerId(substr($peer_id, 4, 5), 'Bits on Wheels');
    } // Bits on Wheels
    if ('ML' == substr($peer_id, 1, 2)) {
        return MainlineDecodePeerId(substr($peer_id, 3, 5), 'MLDonkey');
    } // MLDonkey
    if ('AZ2500BT' == substr($peer_id, 0, 8)) {
        return 'AzureusBitTyrant 1.0/1';
    }
    if ('A' == $peer_id[0]) {
        return StdDecodePeerId(substr($peer_id, 1, 9), 'ABC');
    } // ABC
    if ('R' == $peer_id[0]) {
        return StdDecodePeerId(substr($peer_id, 1, 5), 'Tribler');
    } // Tribler
    if ('M' == $peer_id[0]) {
        if (preg_match('/^Python/', $httpagent, $matches)) {
            return 'Spoofing BT Client';
        } // Spoofing BT Client

        return MainlineDecodePeerId(substr($peer_id, 1, 7), 'Mainline'); // Mainline BitTorrent with version
    }
    if ('O' == $peer_id[0]) {
        return StdDecodePeerId(substr($peer_id, 1, 9), 'Osprey Permaseed');
    } // Osprey Permaseed
    if ('S' == $peer_id[0]) {
        if (preg_match("/^BitTorrent\/3.4.2/", $httpagent, $matches)) {
            return 'Spoofing BT Client';
        } // Spoofing BT Client

        return StdDecodePeerId(substr($peer_id, 1, 9), 'Shad0w'); // Shadow's client
    }
    if ('T' == $peer_id[0]) {
        if (preg_match('/^Python/', $httpagent, $matches)) {
            return 'Spoofing BT Client';
        } // Spoofing BT Client

        return StdDecodePeerId(substr($peer_id, 1, 9), 'BitTornado'); // BitTornado
    }
    if ('U' == $peer_id[0]) {
        return StdDecodePeerId(substr($peer_id, 1, 9), 'UPnP');
    } // UPnP NAT Bit Torrent
    // Azureus / Localhost
    if ('-AZ' == substr($peer_id, 0, 3)) {
        if (preg_match("/^Localhost ([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches)) {
            return "Localhost $matches[1]";
        }
        if (preg_match("/^BitTorrent\/3.4.2/", $httpagent, $matches)) {
            return 'Spoofing BT Client';
        } // Spoofing BT Client
        if (preg_match('/^Python/', $httpagent, $matches)) {
            return 'Spoofing BT Client';
        } // Spoofing BT Client

        return StdDecodePeerId(substr($peer_id, 3, 7), 'Azureus');
    }
    if (preg_match('/Azureus/', $peer_id)) {
        return 'Azureus 2.0.3.2';
    }
    // BitComet/BitLord/BitVampire/Modded FUTB BitComet
    if ('exbc' == substr($peer_id, 0, 4) || 'UTB' == substr($peer_id, 1, 3)) {
        if ('FUTB' == substr($peer_id, 0, 4)) {
            return DecodeVersionString(substr($peer_id, 4, 2), 'BitComet Mod1');
        } elseif ('xUTB' == substr($peer_id, 0, 4)) {
            return DecodeVersionString(substr($peer_id, 4, 2), 'BitComet Mod2');
        } elseif ('LORD' == substr($peer_id, 6, 4)) {
            return DecodeVersionString(substr($peer_id, 4, 2), 'BitLord');
        } elseif ('---' == substr($peer_id, 6, 3) && 'BitComet 0.54' == DecodeVersionString(substr($peer_id, 4, 2), 'BitComet')) {
            return 'BitVampire';
        } else {
            return DecodeVersionString(substr($peer_id, 4, 2), 'BitComet');
        }
    }
    // Rufus
    if ('RS' == substr($peer_id, 2, 2)) {
        for ($i = 0; $i <= strlen(substr($peer_id, 4, 9)); ++$i) {
            $c = $peer_id[$i + 4];
            if (ctype_alnum($c) || $c == chr(0)) {
                $rufus_chk = true;
            } else {
                break;
            }
        }
        if ($rufus_chk) {
            return DecodeVersionString(substr($peer_id, 0, 2), 'Rufus');
        } // Rufus
    }
    // BitSpirit
    if ('HTTPBT' == substr($peer_id, 14, 6) || 'UDP0' == substr($peer_id, 16, 4)) {
        if ('BS' == substr($peer_id, 2, 2)) {
            if ($peer_id[1] == chr(0)) {
                return 'BitSpirit v1';
            }
            if ($peer_id[1] == chr(2)) {
                return 'BitSpirit v2';
            }
        }

        return 'BitSpirit';
    }
    //BitSpirit
    if ('BS' == substr($peer_id, 2, 2)) {
        if ($peer_id[1] == chr(0)) {
            return 'BitSpirit v1';
        }
        if ($peer_id[1] == chr(2)) {
            return 'BitSpirit v2';
        }

        return 'BitSpirit';
    }
    // eXeem beta
    if ('-eX' == substr($peer_id, 0, 3)) {
        $version_str = '';
        $version_str .= intval($peer_id[3], 16) . '.';
        $version_str .= intval($peer_id[4], 16);

        return "eXeem $version_str";
    }
    if ('eX' == substr($peer_id, 0, 2)) {
        return 'eXeem';
    } // eXeem beta .21
    if (substr($peer_id, 0, 12) == (chr(0) * 12) && $peer_id[12] == chr(97) && $peer_id[13] == chr(97)) {
        return 'Experimental 3.2.1b2';
    } // Experimental 3.2.1b2
    if (substr($peer_id, 0, 12) == (chr(0) * 12) && $peer_id[12] == chr(0) && $peer_id[13] == chr(0)) {
        return 'Experimental 3.1';
    } // Experimental 3.1
    //if(substr($peer_id,0,12)==(chr(0)*12)) return "Mainline (obsolete)"; # Mainline BitTorrent (obsolete)
    //return "$httpagent [$peer_id]";
    return 'Unknown client';
}

//========================================
//getAgent function by deliopoulos
//========================================
/**
 * @param $httpagent
 * @param $peer_id
 *
 * @return mixed|string
 */
function getclient($httpagent, $peer_id)
{
    if (preg_match('/^-U([TM])([0-9]{3})([0-9B])-(..)/s', $peer_id, $matches)) {
        $ver      = (int) $matches[2];
        $vere     = $matches[3];
        $beta     = 'B' === $vere;
        $buildnum = $matches[4];
        $buildvar = unpack('v*', $buildnum);
        $buildv   = $buildvar[1];
        if ('M' === $matches[1] || $ver > 180) {
            $build = $buildv;
        } elseif ($ver < 180) {
            $build = $buildv & 16383;
        } else {
            if ($beta && $buildv & 49152) {
                $build = $buildv & 16383;
            } else {
                $build = $buildv;
            }
        }
        if ('M' === $matches[1]) {
            return "\xB5" . 'TorrentMac/' . $matches[2][0] . '.' . $matches[2][1] . '.' . $matches[2][2] . ' (' . $build . ')';
        } else {
            return "\xB5" . 'Torrent/' . $matches[2][0] . '.' . $matches[2][1] . '.' . $matches[2][2] . ' (' . $build . ')';
        }
    }
    if (preg_match('/^Azureus ([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)/', $httpagent, $matches)) {
        return 'Azureus/' . $matches[1];
    }
    if (preg_match('/BitTorrent\\/S-([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches)) {
        return 'Shadows/' . $matches[1];
    }
    if (preg_match('/BitTorrent\\/ABC-([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches)) {
        return 'ABC/' . $matches[1];
    }
    if (preg_match('/ABC-([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches)) {
        return 'ABC/' . $matches[1];
    }
    if (preg_match('/Rufus\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches)) {
        return 'Rufus/' . $matches[1];
    }
    if (preg_match('/BitTorrent\\/U-([0-9]+\\.[0-9]+\\.[0-9]+)/', $httpagent, $matches)) {
        return 'UPnP/' . $matches[1];
    }
    if (preg_match('/^BitTorrent\\/T-(.+)$/', $httpagent, $matches)) {
        return 'BitTornado/' . $matches[1];
    }
    if (preg_match('/^BitTornado\\/T-(.+)$/', $httpagent, $matches)) {
        return 'BitTornado/' . $matches[1];
    }
    if (preg_match('/^BitTorrent\\/brst(.+)/', $httpagent, $matches)) {
        return 'Burst/' . $matches[1];
    }
    if (preg_match('/^RAZA (.+)$/', $httpagent, $matches)) {
        return 'Shareaza/' . $matches[1];
    }
    // Shareaza 2.2.1.0
    if (preg_match('/^Shareaza ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $httpagent, $matches)) {
        return 'Shareaza/' . $matches[1];
    }
    if ('MLdonkey' == substr($httpagent, 0, 8)) {
        return 'MLDonkey/' . substr($httpagent, 9);
    }
    if (preg_match('/^rtorrent\/([0-9]+\.[0-9]+\.[0-9]+)/', $httpagent, $matches)) {
        return 'rTorrent/' . $matches[1];
    }
    if (preg_match('/^Transmission\/([0-9]+\.[0-9]+)/', $httpagent, $matches)) {
        return 'Transmission/' . $matches[1];
    }
    if (preg_match('/^Deluge ((?:[0-9](?:\.[0-9]){1,3}))(?:-.+)?$/', $httpagent, $matches)) {
        return 'Deluge/' . $matches[1];
    }
    //Try to figure it out by peer id
    $short_id = substr($peer_id, 1, 2);
    if ('T' == $peer_id[0]) {
        return 'BitTornado/' . substr($peer_id, 1, 1) . '.' . substr($peer_id, 2, 1) . '.' . substr($peer_id, 3, 1);
    }
    if ('exbc' == substr($peer_id, 0, 4) && 'LORD' == substr($peer_id, 6, 4)) {
        return 'BitLord/' . ord(substr($peer_id, 4, 1)) . '.' . ord(substr($peer_id, 5, 1));
    }
    if ('BC' == $short_id) {
        return 'BitComet/' . ((int) substr($peer_id, 3, 2)) . '.' . ((int) substr($peer_id, 5, 2));
    }
    if ('exbc' == substr($peer_id, 0, 4)) {
        return 'BitComet/' . ord(substr($peer_id, 4, 1)) . '.' . ord(substr($peer_id, 5, 1));
    }
    if ('UTB' == substr($peer_id, 1, 3)) {
        return 'BitComet/' . ord(substr($peer_id, 4, 1)) . '.' . ord(substr($peer_id, 5, 1));
    }
    if ('Mbrst' == substr($peer_id, 0, 5)) {
        return 'Burst/' . substr($peer_id, 5, 1) . '.' . substr($peer_id, 7, 1) . '.' . substr($peer_id, 9, 1);
    }
    if ('BS' == substr($peer_id, 2, 2)) {
        return 'BitSpirit/' . ord(substr($peer_id, 1, 1)) . '.' . ord(substr($peer_id, 0, 1));
    }
    if (preg_match('/^M([0-9])\-([0-9])\-([0-9])/', $peer_id, $matches)) {
        return 'Mainline/' . $matches[1] . '.' . $matches[2] . '.' . $matches[3];
    }
    if ('G3' == $short_id) {
        return 'G3 Torrent';
    }
    if ('AR' == $short_id) {
        return 'Arctic Torrent';
    }
    if ('KT' == $short_id) {
        return 'KTorrent';
    }
    if ('BOW' == substr($peer_id, 1, 3)) {
        return 'Bits on Wheels';
    }
    if ('XBT' == substr($peer_id, 0, 3)) {
        return 'XBT/' . substr($peer_id, 3, 1) . '.' . substr($peer_id, 4, 1) . '.' . substr($peer_id, 5, 1);
    }
    //Regular Old Bittorrent
    if (preg_match('/libtorrent/i', $httpagent, $matches)) {
        return 'LibTorrent';
    }
    if ('Python-urllib' == substr($httpagent, 0, 13)) {
        return 'BitTorrent/' . substr($httpagent, 14);
    }
    if (preg_match('/^BitTorrent\\/([0-9]+(\\.[0-9]+)*)/', $httpagent, $matches)) {
        return 'BitTorrent/' . $matches[1];
    }
    if (preg_match('/^BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches)) {
        return 'BitTorrent/' . $matches[1];
    }
    if (preg_match('/^Python-urllib\\/.+?, BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches)) {
        return 'BitTorrent/' . $matches[1];
    }

    return preg_replace('/[^a-zA-z0-9._-]/', '-', $peer_id);
}
