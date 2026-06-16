import csv
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent
DETAIL_CSV = BASE_DIR / "template-aikens-v-ahli-materi-detail.csv"
REKAP_CSV = BASE_DIR / "hasil-rekap-aikens-v-ahli-materi.csv"

VALIDATOR_COLUMNS = ["Validator 1", "Validator 2", "Validator 3"]
DENOMINATOR = 12  # n(c-1) = 3 x (5-1)


def kategori(v: float) -> str:
    if v >= 0.80:
        return "Validitas tinggi"
    if v >= 0.40:
        return "Validitas sedang"
    return "Validitas rendah"


def keputusan(v: float) -> str:
    if v >= 0.80:
        return "Valid"
    if v >= 0.60:
        return "Valid dengan revisi kecil"
    return "Perlu revisi besar"


def parse_score(value: str) -> int:
    value = (value or "").strip()
    if not value:
        raise ValueError("ada skor validator yang masih kosong")
    score = int(value)
    if score < 1 or score > 5:
        raise ValueError(f"skor di luar rentang 1-5: {score}")
    return score


def main() -> None:
    rows = []
    with DETAIL_CSV.open("r", encoding="utf-8-sig", newline="") as f:
        reader = csv.DictReader(f)
        for row in reader:
            scores = [parse_score(row[col]) for col in VALIDATOR_COLUMNS]
            sum_r = sum(scores)
            sum_s = sum(score - 1 for score in scores)
            v = round(sum_s / DENOMINATOR, 2)
            rows.append(
                {
                    "No": row["No"],
                    "Aspek": row["Aspek"],
                    "Ringkasan Butir": row["Ringkasan Butir"],
                    "Jumlah Skor (sum r)": sum_r,
                    "sum s": sum_s,
                    "V": v,
                    "Kategori": kategori(v),
                    "Keputusan": keputusan(v),
                }
            )

    aspek_groups = {}
    for row in rows:
        aspek_groups.setdefault(row["Aspek"], []).append(row["V"])

    with REKAP_CSV.open("w", encoding="utf-8-sig", newline="") as f:
        fieldnames = ["Aspek", "Jumlah Butir", "Rata-rata V", "Kategori", "Keputusan"]
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()

        all_values = []
        for aspek, values in aspek_groups.items():
            avg_v = round(sum(values) / len(values), 2)
            all_values.extend(values)
            writer.writerow(
                {
                    "Aspek": aspek,
                    "Jumlah Butir": len(values),
                    "Rata-rata V": avg_v,
                    "Kategori": kategori(avg_v),
                    "Keputusan": keputusan(avg_v),
                }
            )

        total_avg = round(sum(all_values) / len(all_values), 2) if all_values else 0
        writer.writerow(
            {
                "Aspek": "Keseluruhan instrumen",
                "Jumlah Butir": len(all_values),
                "Rata-rata V": total_avg,
                "Kategori": kategori(total_avg),
                "Keputusan": keputusan(total_avg),
            }
        )

    print(f"Rekap berhasil dibuat: {REKAP_CSV}")


if __name__ == "__main__":
    main()
