<?php

namespace App\Http\Livewire\Penilaian;

use App\Models\Alternatif;
use App\Models\Kriteria;
use Livewire\Component;

class Index extends Component
{
	public function render()
	{
		$alternatifs = Alternatif::orderBy('kode')->get();
		$kriterias = Kriteria::orderBy('kode')->get();
		return view('livewire.penilaian.index', compact('alternatifs', 'kriterias'));
	}



	public function rende1()
	{
		$alternatifs = $this->proses();
		return view('livewire.proses.index', compact('alternatifs'));
	}


	public function proses()
	{
		$alternatifs = Alternatif::orderBy('kode')->get();
		$kriterias = Kriteria::orderBy('kode')->get()->toArray();
		// dd($kriterias[0]['type']);

		// membuat matriks keputusan
		$matriks = [];
		foreach ($alternatifs as $ka => $alt) {
			foreach ($alt->kriteria as $kk => $krit) {
				$matriks[$kk][$ka] = $krit->pivot->nilai;
			}
		}
		// dd(sqrt(207));

		// normalisasi matriks
		$matriks_normalisasi = [];
		foreach ($matriks as $kb => $baris) {
			$sum = 0;
			foreach ($baris as $kk => $kolom) {
				$sum += pow($kolom, 2);
			}
			$c = sqrt($sum);

			foreach ($baris as $kk => $kolom) {
				$matriks_normalisasi[$kb][] = $kolom / $c;
			}
		}

		// optimalisasi nilai attribute
		$optimalisasi = [];
		foreach ($matriks_normalisasi as $kb => $baris) {
			foreach ($baris as $kk => $kolom) {
				$optimalisasi[$kk][] = $kolom * $kriterias[$kb]['bobot'];
			}
		}

		// mengurangkan nilai min max
		$minmax = [];
		foreach ($optimalisasi as $kb => $baris) {
			$min = 0;
			$max = 0;
			foreach ($baris as $kk => $kolom) {
				if ($kriterias[$kk]['type'] == true) {
					$max += $kolom;
				} else {
					$min += $kolom;
				}
			}
			$Yi = $max - $min;
			$minmax[] = $Yi;
		}

		foreach ($alternatifs as $key => $alternatif) {
			$alternatif->nilai = round($minmax[$key], 4);
		}

		return $alternatifs;
	}

}