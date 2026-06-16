with open('potential_foreign_words.txt', 'r', encoding='utf-8') as f:
    words = [line.strip() for line in f if line.strip()]

false_positives = {
    'acuan', 'agarwal', 'andi', 'daniel', 'daring', 'dll', 'jan', 'kelima', 'nip', 'pend', 
    'sma', 'urnal', 'val', 'vis', 'vol', 'chi', 'gall', 'joseph', 'makassar', 'nguyen', 'shah', 
    'singh', 'tran', 'yun', 'bro', 'brooke', 'dixit'
}

filtered_words = [w for w in words if w not in false_positives]

with open('foreign_words_to_italicize.txt', 'w', encoding='utf-8') as f:
    for w in filtered_words:
        f.write(f"{w}\n")

print(f"Filtered down to {len(filtered_words)} words.")
